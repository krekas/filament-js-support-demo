<?php

namespace App\Filament\Pages;

use App\Enums\EventStatus;
use App\Models\Event;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('event_id')
                            ->label('Event')
                            ->options(fn (): array => Event::query()->orderBy('starts_at')->pluck('name', 'id')->all())
                            ->default(fn (): ?int => self::defaultEventId())
                            ->selectablePlaceholder(false)
                            ->native(false),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * The next on-sale event, falling back to the next upcoming event that isn't cancelled.
     */
    public static function defaultEventId(): ?int
    {
        $nextOnSaleEventId = Event::query()
            ->onSale()
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->value('id');

        return $nextOnSaleEventId ?? Event::query()
            ->where('status', '!=', EventStatus::Cancelled)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->value('id');
    }
}
