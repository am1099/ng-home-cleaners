@extends('layouts.public')

@section('content')
    <x-public.page-hero :title="$page->title" />

    <x-public.section spacing="follow">
        <x-public.container narrow>
            <div class="prose-ng max-w-none text-ink-muted">
                {!! nl2br(e($page->content)) !!}
            </div>
        </x-public.container>
    </x-public.section>
@endsection
