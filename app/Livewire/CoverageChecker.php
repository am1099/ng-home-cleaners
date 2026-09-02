<?php

namespace App\Livewire;

use App\Services\CoverageService;
use Livewire\Component;

class CoverageChecker extends Component
{
    public string $variant = 'card';

    public string $postcode = '';

    public ?bool $covered = null;

    public ?string $message = null;

    public ?string $areaName = null;

    public ?string $areaUrl = null;

    public function check(CoverageService $coverage): void
    {
        $this->validate([
            'postcode' => ['required', 'string', 'max:12'],
        ]);

        $result = $coverage->check($this->postcode);

        $this->covered = $result['covered'];
        $this->message = $result['message'];
        $this->areaName = $result['area']?->name;
        $this->areaUrl = $result['area'] ? route('areas.show', $result['area']) : null;
    }

    public function render()
    {
        return view('livewire.coverage-checker');
    }
}
