<x-mail::message>
# {{ $subject }}

{!! nl2br(e($content)) !!}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
