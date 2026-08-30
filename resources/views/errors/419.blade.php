@extends('layouts.error-minimal')

@section('title', 'Page expired')

@section('content')
    <p class="eyebrow">Error 419</p>
    <h1>Your session has expired.</h1>
    <p>
        For security, the form you submitted is no longer valid. Please go back and try again — your details have not been lost if they are still on the previous page.
    </p>
    <div class="actions">
        <a class="btn primary" href="javascript:history.back()">Go back</a>
        <a class="btn outline" href="/">Homepage</a>
        <a class="btn outline" href="/get-a-quote">Get a free estimate</a>
    </div>
@endsection
