<?php

namespace App\Filament\Widgets;

use App\Services\DashboardMetrics;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class RecentActivityWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Recent activity';

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.recent-activity';

    /**
     * @return list<array{type: string, title: string, subtitle: string, at: Carbon, url: string|null}>
     */
    public function getActivities(): array
    {
        return app(DashboardMetrics::class)->recentActivity(8)->all();
    }
}
