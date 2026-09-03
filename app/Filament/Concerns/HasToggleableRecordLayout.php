<?php

namespace App\Filament\Concerns;

use Illuminate\Support\Str;

trait HasToggleableRecordLayout
{
    public string $tableLayoutView = 'list';

    public function mountHasToggleableRecordLayout(): void
    {
        $saved = session()->get($this->tableLayoutSessionKey());

        if (in_array($saved, ['list', 'grid'], true)) {
            $this->tableLayoutView = $saved;

            return;
        }

        $this->tableLayoutView = $this->requestLooksMobile() ? 'grid' : 'list';
    }

    public function setTableLayoutView(string $layout): void
    {
        $this->tableLayoutView = in_array($layout, ['list', 'grid'], true) ? $layout : 'list';

        session()->put($this->tableLayoutSessionKey(), $this->tableLayoutView);

        // Table schema is built during boot (before actions run), so rebuild it
        // after the layout preference changes.
        $this->table = $this->table($this->makeTable());
    }

    public function isGridLayout(): bool
    {
        return $this->tableLayoutView === 'grid';
    }

    public function isListLayout(): bool
    {
        return ! $this->isGridLayout();
    }

    protected function tableLayoutSessionKey(): string
    {
        return 'ng.admin.tableLayout.'.$this->getTableLayoutPersistenceKey();
    }

    protected function getTableLayoutPersistenceKey(): string
    {
        return Str::of(static::class)->classBasename()->snake()->toString();
    }

    protected function requestLooksMobile(): bool
    {
        $agent = strtolower((string) request()->userAgent());

        if ($agent === '') {
            return false;
        }

        return (bool) preg_match('/mobile|android|iphone|ipad|ipod|webos|blackberry|opera mini/i', $agent);
    }
}
