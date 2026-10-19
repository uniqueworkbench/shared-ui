<x-mail::message>
@if ($errorLog->handled)
# Handled error in {{ $errorLog->app }} ({{ $errorLog->environment }})

The app caught this and carried on — the request did not fail.
@else
# {{ $errorLog->status_code ?? 500 }} error in {{ $errorLog->app }} ({{ $errorLog->environment }})
@endif

**{{ $errorLog->exception_class }}**

{{ $errorLog->message }}

@if ($errorLog->url)
**URL:** {{ $errorLog->method }} {{ $errorLog->url }}
@endif
@if ($errorLog->user_id)
**User ID:** {{ $errorLog->user_id }}
@endif
@if ($errorLog->account_id)
**Account ID:** {{ $errorLog->account_id }}
@endif
@if ($errorLog->file)
**Location:** {{ $errorLog->file }}:{{ $errorLog->line }}
@endif

<x-mail::panel>
{{ $errorLog->trace }}
</x-mail::panel>

Error log #{{ $errorLog->id }}, recorded {{ $errorLog->created_at }}.
</x-mail::message>
