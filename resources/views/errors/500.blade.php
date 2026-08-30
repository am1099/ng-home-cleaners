@extends('layouts.error-minimal')

@section('title', 'Something went wrong')

@section('content')
    <p class="eyebrow">Error 500</p>
    <h1>Something went wrong on our side.</h1>
    <p>
        We could not complete that request. Please try again in a moment. If you need us straight away, call or WhatsApp us and we will help.
    </p>
    <div class="actions">
        <a class="btn primary" href="/">Back to homepage</a>
        <a class="btn outline" href="/contact">Contact us</a>
        <a class="btn outline" href="/get-a-quote">Get a free estimate</a>
    </div>
@endsection
