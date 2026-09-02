<?php

namespace App\Models;

use App\Enums\EmailTemplateKey;
use App\Services\EmailTemplateService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key',
    'name',
    'description',
    'subject',
    'heading',
    'body',
])]
class EmailTemplate extends Model
{
    protected static function booted(): void
    {
        static::updating(function (EmailTemplate $template): void {
            if ($template->isDirty('key')) {
                $template->key = $template->getOriginal('key');
            }
        });

        static::deleting(function (): false {
            return false;
        });
    }

    protected function casts(): array
    {
        return [
            'key' => EmailTemplateKey::class,
        ];
    }

    public static function for(EmailTemplateKey $key): self
    {
        return app(EmailTemplateService::class)->template($key);
    }

    public function renderSubject(array $variables): string
    {
        return app(EmailTemplateService::class)->replace($this->subject, $variables);
    }

    public function renderHeading(array $variables): string
    {
        return app(EmailTemplateService::class)->replace((string) $this->heading, $variables);
    }

    public function renderBodyHtml(array $variables): string
    {
        return app(EmailTemplateService::class)->renderBodyHtml($this->body, $variables);
    }
}
