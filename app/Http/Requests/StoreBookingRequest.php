<?php

namespace App\Http\Requests;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public const MAX_SEATS = 8;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'seats' => ['required', 'array', 'min:1', 'max:'.self::MAX_SEATS],
            'seats.*' => ['required', 'string', 'distinct'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'seats.required' => 'Please choose at least one seat.',
            'seats.min' => 'Please choose at least one seat.',
            'seats.max' => 'You can book up to '.self::MAX_SEATS.' seats at a time.',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $event = $this->route('event');

                if ($event instanceof Event && $event->status !== EventStatus::OnSale) {
                    $validator->errors()->add('seats', 'Tickets for this event are not on sale.');
                }
            },
        ];
    }
}
