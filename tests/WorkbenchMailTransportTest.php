<?php

namespace UniqueWorkbench\SharedUi\Tests;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use UniqueWorkbench\SharedUi\Mail\WorkbenchMailTransport;

class WorkbenchMailTransportTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('mail.default', 'workbench');
    }

    private function fakeRelay(): void
    {
        Cache::put('workbench-client-token.mail', 'token', 600);
        Http::fake(['account.test/oauth/token' => Http::response(['access_token' => 'token', 'expires_in' => 3600]), 'account.test/api/mail' => Http::response(['sent' => true], 202)]);
    }

    private function mailable(?string $tag): Mailable
    {
        return new class($tag) extends Mailable {
            public function __construct(private ?string $tag) {}

            public function envelope(): Envelope
            {
                return new Envelope(subject: 'Hello');
            }

            public function content(): Content
            {
                return new Content(htmlString: '<p>Hi</p>');
            }

            public function headers(): Headers
            {
                return new Headers(text: $this->tag ? [WorkbenchMailTransport::REPLY_TAG_HEADER => $this->tag] : []);
            }
        };
    }

    public function test_a_reply_tag_header_goes_to_the_relay_as_reply_tag(): void
    {
        $this->fakeRelay();

        Mail::to('quoter@example.com')->send($this->mailable('abc123'));

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/mail')
            && $request['reply_tag'] === 'abc123' && $request['to'] === ['quoter@example.com']);
    }

    public function test_without_the_header_no_reply_tag_is_sent(): void
    {
        $this->fakeRelay();

        Mail::to('quoter@example.com')->send($this->mailable(null));

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/mail') && ! array_key_exists('reply_tag', $request->data()));
    }
}
