<?php

namespace App\Livewire\Concerns;

use Illuminate\Validation\ValidationException;

trait FlashesToasts
{
    protected function toastSuccess(string $title, ?string $message = null): void
    {
        $this->dispatchToast('success', $title, $message);
    }

    protected function toastError(string $title, ?string $message = null): void
    {
        $this->dispatchToast('error', $title, $message);
    }

    protected function toastInfo(string $title, ?string $message = null): void
    {
        $this->dispatchToast('info', $title, $message);
    }

    /**
     * Turn Livewire validation failures into corner toasts.
     * Field errors stay inline; submit / summary messages toast.
     */
    protected function toastFromValidation(
        ValidationException $exception,
        string $fallbackTitle = 'Check the form',
        string $fallbackMessage = 'Please check the highlighted fields.',
        string $submitTitle = 'Something went wrong',
    ): void {
        $submit = $exception->errors()['submit'][0] ?? null;

        if (is_string($submit) && $submit !== '') {
            $this->toastError($submitTitle, $submit);

            return;
        }

        $this->toastError($fallbackTitle, $fallbackMessage);
    }

    protected function dispatchToast(string $type, string $title, ?string $message = null): void
    {
        $this->dispatch('toast',
            type: $type,
            title: $title,
            message: $message,
        );
    }
}
