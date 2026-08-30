<h2 class="ng-display text-2xl">Review your estimate request</h2>
<p class="mt-2 text-sm text-ink-muted">Check everything looks right before you send it.</p>

<div class="mt-6 rounded-[var(--radius-md)] border border-border bg-surface-sunken/40 p-5">
    @include('livewire.partials.estimate-summary', ['expanded' => true])
</div>

@if ($this->calculation)
    <div class="mt-6 rounded-[var(--radius-md)] border border-brand-100 bg-brand-50/50 p-5">
        <p class="text-sm font-semibold uppercase tracking-[var(--tracking-eyebrow)] text-brand-700">Guide estimate</p>
        <p class="ng-display mt-2 text-2xl text-brand-900">{{ $this->calculation->displayHeadline }}</p>
        @if ($this->calculation->displayDetail)
            <p class="mt-2 text-sm text-ink-muted">{{ $this->calculation->displayDetail }}</p>
        @endif
    </div>
@endif

<p class="mt-6 text-sm text-ink-muted">
    By sending this request, you are asking for a written quote. Nothing is booked and no payment is taken at this stage.
    You can send your request by email using the button below, or continue on WhatsApp if you prefer to add details in chat.
</p>
