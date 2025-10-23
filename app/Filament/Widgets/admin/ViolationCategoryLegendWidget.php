<?php

namespace App\Filament\Widgets\Admin;

use Filament\Widgets\Widget;

class ViolationCategoryLegendWidget extends Widget
{
    protected string $view = 'filament.widgets.violation-category-legend-widget';
    public function getColumnSpan(): int|string|array
{
    return 12;
}

}
