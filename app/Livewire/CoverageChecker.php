<?php

namespace App\Livewire;

use App\Livewire\Concerns\FlashesToasts;
use App\Services\CoverageService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CoverageChecker extends Component
{
    use FlashesToasts;

    public string $variant = 'card';

    public string $postcode = '';

    public ?bool $covered = null;

    public ?string $message = null;

    public ?string $areaName = null;

    public ?string $areaUrl = null;

    public function check(CoverageService $coverage): void
    {
        try {
            $this->validate([
                'postcode' => ['required', 'string', 'max:12'],
            ]);
        } catch (ValidationException $exception) {
            $this->toastError('Postcode needed', 'Enter a district or full postcode to check coverage.');

            throw $exception;
        }

        $result = $coverage->check($this->postcode);

        $this->covered = $result['covered'];
        $this->message = $result['message'];
        $this->areaName = $result['area']?->name;
        $this->areaUrl = $result['area'] ? route('areas.show', $result['area']) : null;

        if ($this->covered) {
            $this->toastSuccess('You\'re in our area', $this->message);
        } else {
            $this->toastInfo('Outside our usual area', $this->message);
        }
    }

    public function render()
    {
        return view('livewire.coverage-checker');
    }
}
