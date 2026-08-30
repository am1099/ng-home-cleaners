<?php

namespace App\Enums;

use App\Models\Service;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

enum ServiceIcon: string implements HasLabel
{
    case House = 'house';
    case Sparkles = 'sparkles';
    case Key = 'key';
    case Building = 'building-2';
    case HomeModern = 'home-modern';
    case PaintBrush = 'paint-brush';
    case Droplet = 'droplet';
    case Sun = 'sun';
    case ShieldCheck = 'shield-check';
    case Star = 'star';
    case Leaf = 'leaf';
    case WrenchScrewdriver = 'wrench-screwdriver';
    case Clock = 'clock';
    case MapPin = 'map-pin';
    case CheckBadge = 'check-badge';
    case HandRaised = 'hand-raised';
    case Sparkle = 'sparkle';
    case BuildingOffice = 'building-office';
    case Cube = 'cube';
    case Beaker = 'beaker';

    public function getLabel(): string
    {
        return match ($this) {
            self::House => 'House',
            self::Sparkles => 'Sparkles',
            self::Key => 'Key',
            self::Building => 'Building',
            self::HomeModern => 'Modern home',
            self::PaintBrush => 'Paint brush',
            self::Droplet => 'Droplet',
            self::Sun => 'Sun',
            self::ShieldCheck => 'Shield check',
            self::Star => 'Star',
            self::Leaf => 'Leaf',
            self::WrenchScrewdriver => 'Tools',
            self::Clock => 'Clock',
            self::MapPin => 'Map pin',
            self::CheckBadge => 'Check badge',
            self::HandRaised => 'Hand raised',
            self::Sparkle => 'Sparkle',
            self::BuildingOffice => 'Office',
            self::Cube => 'Cube',
            self::Beaker => 'Beaker',
        };
    }

    /** @deprecated Use getLabel() */
    public function label(): string
    {
        return $this->getLabel();
    }

    public function path(): string
    {
        return match ($this) {
            self::House => 'm2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
            self::Sparkles => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z',
            self::Key => 'M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499a1.875 1.875 0 0 1 1.591-.659H15.75Z',
            self::Building => 'M3.75 21h16.5M4.875 21V9.375a1.125 1.125 0 0 1 1.125-1.125h3.375a1.125 1.125 0 0 1 1.125 1.125V21M9.75 21V5.625A1.125 1.125 0 0 1 10.875 4.5h2.25A1.125 1.125 0 0 1 14.25 5.625V21M19.125 21V9.375a1.125 1.125 0 0 0-1.125-1.125h-3.375a1.125 1.125 0 0 0-1.125 1.125V21',
            self::HomeModern => 'M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m-3-1V7.25a.75.75 0 0 0-.75-.75h-3.5a.75.75 0 0 0-.75.75v3.75m0 0-3-1.091',
            self::PaintBrush => 'M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42',
            self::Droplet => 'M12 21.75c4.556 0 8.25-3.694 8.25-8.25 0-4.004-2.442-7.38-5.25-9.75L12 1.5 8.999 3.75C6.191 6.12 3.75 9.496 3.75 13.5c0 4.556 3.694 8.25 8.25 8.25Z',
            self::Sun => 'M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z',
            self::ShieldCheck => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
            self::Star => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z',
            self::Leaf => 'M21 10.5h.006v.006H21V10.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM4.136 14.209a.375.375 0 0 1 .375-.375h.008a.375.375 0 0 1 0 .75h-.008a.375.375 0 0 1-.375-.375Zm0 0a.375.375 0 0 1 .375-.375h.008a.375.375 0 0 1 0 .75h-.008a.375.375 0 0 1-.375-.375ZM12 21.75c-2.485 0-4.5-2.015-4.5-4.5 0-2.071 1.5-4.5 4.5-7.5 3 3 4.5 5.429 4.5 7.5 0 2.485-2.015 4.5-4.5 4.5Z',
            self::WrenchScrewdriver => 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z',
            self::Clock => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            self::MapPin => 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z',
            self::CheckBadge => 'M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z',
            self::HandRaised => 'M10.05 4.575a1.575 1.575 0 1 0-3.15 0v3m3.15-3v-1.5a1.575 1.575 0 0 1 3.15 0v1.5m-3.15 0 .075 5.925m3.075-5.925v-1.5a1.575 1.575 0 0 1 3.15 0v1.5m-3.15 0 .075 5.925m3.075-5.925a1.575 1.575 0 1 1 3.15 0v3.375c0 .621-.504 1.125-1.125 1.125h-6.75a1.125 1.125 0 0 1-1.125-1.125V9.15m9.3-4.5a1.575 1.575 0 0 1 3.15 0v5.25a9 9 0 1 1-18 0V9.15',
            self::Sparkle => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z',
            self::BuildingOffice => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
            self::Cube => 'm21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9',
            self::Beaker => 'M9.75 3.104v5.696a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 8.25 0m0 0v5.696a2.25 2.25 0 0 0 .659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5',
        };
    }

    public function svg(string $class = 'size-5'): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="'.$class.'" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="'.$this->path().'"/></svg>';
    }

    public function filamentOptionLabel(): Htmlable
    {
        return new HtmlString(
            '<span class="inline-flex items-center gap-2">'.$this->svg('size-5 shrink-0').'<span>'.e($this->getLabel()).'</span></span>'
        );
    }

    /**
     * @return array<string, string>
     */
    public static function filamentLabels(?string $keepValue = null): array
    {
        return collect(self::filamentOptions($keepValue))
            ->mapWithKeys(fn (Htmlable $html, string $value): array => [
                $value => self::from($value)->getLabel(),
            ])
            ->all();
    }

    /**
     * @return array<string, Htmlable>
     */
    public static function filamentIcons(?string $keepValue = null): array
    {
        return collect(self::filamentOptions($keepValue))
            ->mapWithKeys(fn (Htmlable $html, string $value): array => [
                $value => new HtmlString(self::from($value)->svg('size-6')),
            ])
            ->all();
    }

    /**
     * @return array<string, Htmlable>
     */
    public static function filamentOptions(?string $keepValue = null): array
    {
        $used = Service::query()
            ->when(
                filled($keepValue),
                fn ($query) => $query->where('icon', '!=', $keepValue),
                fn ($query) => $query,
            )
            ->pluck('icon')
            ->map(fn ($icon) => $icon instanceof self ? $icon->value : (string) $icon)
            ->all();

        $options = [];

        foreach (self::cases() as $case) {
            if (in_array($case->value, $used, true) && $case->value !== $keepValue) {
                continue;
            }

            $options[$case->value] = $case->filamentOptionLabel();
        }

        return $options;
    }
}
