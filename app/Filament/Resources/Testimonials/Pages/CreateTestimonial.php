<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Filament\Concerns\RedirectsToViewOrIndexAfterCreate;
use App\Filament\Resources\Testimonials\TestimonialResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTestimonial extends CreateRecord
{
    use RedirectsToViewOrIndexAfterCreate;

    protected static string $resource = TestimonialResource::class;
}
