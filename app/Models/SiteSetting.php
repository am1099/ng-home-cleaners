<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'business_name',
    'home_hero_title',
    'home_hero_subtitle',
    'home_hero_image',
    'home_hero_image_alt',
    'about_hero_title',
    'about_hero_subtitle',
    'about_story',
    'about_promises',
    'how_it_works_steps',
    'why_choose_items',
    'logo_path',
    'favicon_path',
    'phone',
    'email',
    'whatsapp_number',
    'whatsapp_url',
    'lead_notification_emails',
    'opening_hours',
    'service_area_summary',
    'business_address',
    'google_business_url',
    'social_links',
    'show_google_reviews',
    'show_dbs_statement',
    'dbs_statement',
    'show_insurance_statement',
    'insurance_amount',
    'insurance_statement',
    'guarantee_statement',
    'show_recent_work',
    'default_seo_title',
    'default_seo_description',
    'default_og_image',
])]
class SiteSetting extends Model
{
    protected function casts(): array
    {
        return [
            'lead_notification_emails' => 'array',
            'opening_hours' => 'array',
            'social_links' => 'array',
            'about_promises' => 'array',
            'how_it_works_steps' => 'array',
            'why_choose_items' => 'array',
            'show_google_reviews' => 'boolean',
            'show_dbs_statement' => 'boolean',
            'show_insurance_statement' => 'boolean',
            'show_recent_work' => 'boolean',
        ];
    }

    public static function instance(): self
    {
        return static::query()->firstOrCreate(
            [],
            [
                'business_name' => config('ng.name'),
                'phone' => config('ng.phone'),
                'email' => config('ng.email'),
                'whatsapp_number' => config('ng.phone'),
                'whatsapp_url' => config('ng.whatsapp_url'),
                'service_area_summary' => config('ng.service_area'),
                'opening_hours' => ['summary' => config('ng.hours')],
            ],
        );
    }

    public function phoneTel(): string
    {
        return 'tel:'.$this->phone;
    }

    public function phoneDisplay(): string
    {
        $digits = preg_replace('/\D/', '', $this->phone);

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return substr($digits, 0, 5).' '.substr($digits, 5);
        }

        return $this->phone;
    }

    public function hoursSummary(): string
    {
        return $this->opening_hours['summary'] ?? '';
    }

    public function whatsappLink(): ?string
    {
        return $this->whatsapp_url ?: null;
    }

    public function whatsappUrlWithMessage(string $message): string
    {
        $base = $this->whatsapp_url;

        if (blank($base)) {
            $digits = preg_replace('/\D/', '', (string) $this->whatsapp_number);

            if (str_starts_with($digits, '0')) {
                $digits = '44'.substr($digits, 1);
            }

            $base = 'https://wa.me/'.$digits;
        }

        $separator = str_contains($base, '?') ? '&' : '?';

        return $base.$separator.'text='.rawurlencode($message);
    }
}
