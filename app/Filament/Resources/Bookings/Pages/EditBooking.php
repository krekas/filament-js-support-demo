<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Actions\SyncBookingSeats;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @property Booking $record
 */
class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * `seats` is not a column, so it is filled from the booking's seat rows.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['seats'] = $this->record->seats()->pluck('seat_key')->all();

        return $data;
    }

    /**
     * @param  Booking  $record
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Booking
    {
        $record->fill(Arr::except($data, ['seats']));

        /** @var list<string> $seatKeys */
        $seatKeys = $data['seats'] ?? [];

        return app(SyncBookingSeats::class)->handle($record, $seatKeys, errorKey: 'data.seats');
    }
}
