{{-- Boots session-flash toasts after full page loads / redirects. --}}
@php
    $toast = session('toast');
    $payload = is_array($toast) ? [
        'type' => $toast['type'] ?? 'success',
        'title' => $toast['title'] ?? null,
        'message' => $toast['message'] ?? null,
    ] : null;
@endphp

@if ($payload && (filled($payload['title']) || filled($payload['message'])))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.ngToast?.(@json($payload));
        });
    </script>
@endif
