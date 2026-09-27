<x-mail::message>
# {{ $errorLog->status_code ?? 500 }} error in {{ $errorLog->app }} ({{ $errorLog->environment }})

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
