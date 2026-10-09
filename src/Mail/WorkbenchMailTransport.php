<?php

namespace UniqueWorkbench\SharedUi\Mail;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\Part\DataPart;
use UniqueWorkbench\SharedUi\Workbench\ClientToken;

/**
 * The `workbench` mail driver (MAIL_MAILER=workbench): the app's email goes out through the account
 * app's mail relay (POST <sso.base_url>/api/mail, client credentials, scope `mail`), so the app needs
 * no mail provider settings of its own — only the account app has them. Mail comes from the account
 * app's address; this app's from *name* and reply-to are kept. Recipients' names aren't sent (just
 * addresses), and inline images go as ordinary attachments. Failures throw TransportException, like
 * any mail driver.
 */
class WorkbenchMailTransport extends AbstractTransport
{
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $addresses = fn (array $list) => array_map(fn (Address $address) => $address->getAddress(), $list);
        $replyTo = $email->getReplyTo()[0] ?? null;

        $payload = array_filter([
            'to' => $addresses($email->getTo()),
            'cc' => $addresses($email->getCc()),
            'bcc' => $addresses($email->getBcc()),
            'from_name' => ($email->getFrom()[0] ?? null)?->getName() ?: null,
            'reply_to' => $replyTo?->getAddress(),
            'reply_to_name' => $replyTo?->getName() ?: null,
            'subject' => (string) $email->getSubject(),
            'html' => $this->body($email->getHtmlBody()),
            'text' => $this->body($email->getTextBody()),
            'attachments' => array_map(fn (DataPart $part) => [
                'filename' => $part->getFilename() ?? 'attachment',
                'content' => base64_encode($part->getBody()),
                'mime' => $part->getMediaType() . '/' . $part->getMediaSubtype(),
            ], $email->getAttachments()),
        ], fn ($value) => $value !== null && $value !== []);

        $this->post($payload);
    }

    private function post(array $payload, bool $retry = true): void
    {
        try {
            Http::withToken(ClientToken::get('mail'))->acceptJson()->timeout(30)
                ->post(rtrim((string) config('sso.base_url'), '/') . '/api/mail', $payload)
                ->throw();
        } catch (RequestException $e) {
            if ($retry && $e->response->status() === 401) {
                ClientToken::forget('mail');

                $this->post($payload, false);

                return;
            }
            throw new TransportException('Unique Workbench mail relay: ' . ($e->response->json('message') ?? $e->getMessage()), 0, $e);
        } catch (\Throwable $e) {
            throw new TransportException('Unique Workbench mail relay: ' . $e->getMessage(), 0, $e);
        }
    }

    /** A body as a string (Symfony allows a resource) */
    private function body(mixed $body): ?string
    {
        return is_resource($body) ? stream_get_contents($body) : $body;
    }

    public function __toString(): string
    {
        return 'workbench';
    }
}
