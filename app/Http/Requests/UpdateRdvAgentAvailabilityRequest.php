<?php

namespace App\Http\Requests;

use App\Services\RencontreAvailabilityService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRdvAgentAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var RencontreAvailabilityService $availability */
        $availability = app(RencontreAvailabilityService::class);

        $minDate = now()->toDateString();
        $maxDate = now()->addMonths(6)->toDateString();
        $maxWeekday = $availability->allowsWeekends() ? 7 : 5;

        return [
            'recurring' => ['nullable', 'array'],
            'recurring.*.weekday' => ['required', 'integer', 'between:1,'.$maxWeekday],
            'recurring.*.start_time' => ['required', 'date_format:H:i'],
            'recurring.*.end_time' => ['required', 'date_format:H:i'],

            'dates' => ['nullable', 'array'],
            'dates.*.available_on' => ['required', 'date', 'after_or_equal:'.$minDate, 'before_or_equal:'.$maxDate],
            'dates.*.start_time' => ['required', 'date_format:H:i'],
            'dates.*.end_time' => ['required', 'date_format:H:i'],

            'blocks' => ['nullable', 'array'],
            'blocks.*.available_on' => ['nullable', 'date', 'after_or_equal:'.$minDate, 'before_or_equal:'.$maxDate],
            'blocks.*.weekday' => ['nullable', 'integer', 'between:1,'.$maxWeekday],
            'blocks.*.start_time' => ['nullable', 'date_format:H:i'],
            'blocks.*.end_time' => ['nullable', 'date_format:H:i'],
            'blocks.*.all_day' => ['nullable', 'boolean'],
        ];
    }
}
