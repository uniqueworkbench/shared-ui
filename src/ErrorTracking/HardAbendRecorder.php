<?php

namespace UniqueWorkbench\SharedUi\ErrorTracking;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Records uncaught exceptions ("hard abends") to the error_logs table and
 * emails an alert, and renders formatted error pages for 500/403/404.
 * Registered per-app from bootstrap/app.php:
 *
 *   ->withExceptions(function (Exceptions $exceptions) {
 *       HardAbendRecorder::register($exceptions);
 *   })
 *
 * Laravel already skips its own reportable() hook for routine HTTP exceptions
 * (403/404/419/422/etc. all extend Symfony's HttpException, which is in
 * Handler::$internalDontReport) — so genuine unhandled 500s get recorded via
 * the reportable() callback below, while 403s are recorded explicitly in the
 * render() callback instead, since that's the only hook that ever sees them.
 *
 * 404s are NOT recorded/emailed by default (too high-volume: bots, dead
 * links, scanners) — they only get the formatted page. The exception is a
 * 404 hit by an authenticated user, which is a real signal (a broken internal
 * link, a stale bookmark after a route rename) rather than noise, so those
 * ARE recorded and emailed like a 403 would be.
 *
 * Anonymous 403s from Laravel's storage-serving route are excluded the same
 * way: that route 403s any unsigned request before checking file existence,
 * so bots probing for exposed `.env`/`.git` files trigger it constantly on
 * non-production environments (there it 404s instead — see ServeFile's
 * isProduction check — which is why this tends to show up only on dev/stage).
 * An authenticated user hitting it is still recorded/emailed as a real 403.
 */
class HardAbendRecorder
{
    public static function register(Exceptions $exceptions): void
    {
        $recordedIds = [];

        $exceptions->reportable(function (Throwable $e) use (&$recordedIds) {
            $errorLog = static::record($e, request());

            if ($errorLog) {
                $recordedIds[spl_object_id($e)] = $errorLog->id;
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) use (&$recordedIds) {
            return static::renderErrorPage($e, $request, $recordedIds);
        });
    }

    protected static function renderErrorPage(Throwable $e, Request $request, array &$recordedIds): mixed
    {
        // API/JSON consumers keep their normal JSON error responses.
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        // AuthenticationException (a guest hitting an `auth`-protected route
        // — an expired session, a stale bookmark, a bot) doesn't implement
        // HttpExceptionInterface, so the $status computation below would
        // otherwise default it to 500 and treat completely routine traffic
        // as a hard crash: recorded, emailed, AND rendered as the generic
        // hard-abend page instead of Laravel's own built-in redirect-to-
        // login handling for it. Bail out here so that default behavior
        // runs, same as every other routine 4xx already excluded below.
        if ($e instanceof AuthenticationException) {
            return null;
        }

        // Same trap as AuthenticationException above: ValidationException
        // (bad login credentials, failed form validation, etc.) doesn't
        // implement HttpExceptionInterface either, so it would otherwise
        // default to 500 and get recorded/emailed/rendered as a hard crash
        // instead of Laravel's normal redirect-back-with-errors behavior.
        if ($e instanceof ValidationException) {
            return null;
        }

        $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

        if ($status === 404) {
            // Only worth recording when a logged-in user hits it — anonymous
            // 404 traffic is almost entirely bots/scanners/dead links.
            $errorId = $request->user()
                ? static::record($e, $request)?->id
                : null;

            return response()->view('shared-ui::errors.404', ['errorId' => $errorId], 404);
        }

        if ($status === 403 && ! $request->user() && static::isStorageProbe($request)) {
            // Laravel's storage-serving route (registered whenever a disk has
            // `serve => true`) 403s any unsigned request before even checking
            // whether the file exists — so scanners probing for exposed
            // `.env`/`.git` files etc. hit this constantly. The 403 is the
            // signature check correctly doing its job, not a real security
            // event, so treat it like anonymous 404 noise above. An
            // authenticated user hitting this would be a real signal, so
            // that case still falls through to the normal recording below.
            return response()->view('shared-ui::errors.403', ['errorId' => null], 403);
        }

        // Leave every other routine HTTP status (401/419/422/429/etc.) to the
        // app's own renderers or Laravel's defaults.
        if ($status !== 403 && $status < 500) {
            return null;
        }

        // Let local dev see the full debug trace for genuine crashes.
        if ($status >= 500 && config('app.debug')) {
            return null;
        }

        // A real 500 crash was already recorded via the reportable() callback
        // above (same exception instance) — don't record it twice. A 403
        // never reaches reportable() (HttpException is in Laravel's internal
        // dontReport list), so it's recorded here for the first time.
        $errorId = $recordedIds[spl_object_id($e)] ?? static::record($e, $request)?->id;

        return response()->view(
            $status === 403 ? 'shared-ui::errors.403' : 'shared-ui::errors.hard-abend',
            ['errorId' => $errorId],
            $status
        );
    }

    /**
     * True for Laravel's built-in local-disk file-serving route
     * (`Route::name('storage.'.$disk)`, from FilesystemServiceProvider),
     * which is what throws the unsigned-request 403 handled above.
     */
    protected static function isStorageProbe(Request $request): bool
    {
        return str_starts_with((string) $request->route()?->getName(), 'storage.');
    }

    public static function record(Throwable $e, ?Request $request = null): ?ErrorLog
    {
        try {
            $errorLog = ErrorLog::create([
                'app' => config('app.name'),
                'environment' => app()->environment(),
                'exception_class' => get_class($e),
                'status_code' => $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500,
                'message' => Str::limit($e->getMessage(), 2000),
                // string(255) columns: a long URL (e.g. an OAuth callback's code + state) must not stop the recording
                'file' => $e->getFile() ? mb_substr($e->getFile(), 0, 255) : null,
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => $request ? mb_substr($request->fullUrl(), 0, 255) : null,
                'method' => $request?->method(),
                'user_id' => $request?->user()?->id,
                'account_id' => $request?->user()?->account_id,
                'ip_address' => $request?->ip(),
            ]);
        } catch (Throwable $recordingFailure) {
            Log::error('HardAbendRecorder failed to record error: '.$recordingFailure->getMessage());

            return null;
        }

        static::notify($errorLog);

        return $errorLog;
    }

    protected static function notify(ErrorLog $errorLog): void
    {
        $to = config('shared-ui.error_alert_email');

        if (! $to) {
            return;
        }

        try {
            Mail::to($to)->send(new HardAbendMail($errorLog));
            $errorLog->update(['notified_at' => now()]);
        } catch (Throwable $mailFailure) {
            Log::error('HardAbendRecorder failed to send alert email: '.$mailFailure->getMessage());
        }
    }
}
