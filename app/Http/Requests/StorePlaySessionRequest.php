<?php

namespace App\Http\Requests;

use App\Enums\GameType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlaySessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $gameType = $this->input('game_type');
        $maxPlayers = $gameType === GameType::DrivingSimulator->value ? 2 : 4;
        $isBirthday = $this->boolean('is_birthday_offer');

        return [
            'station_id' => ['required', 'exists:stations,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'game_type' => ['required', Rule::enum(GameType::class)],
            'player_count' => ['required', 'integer', 'min:1', 'max:'.$maxPlayers],
            'pricing_package_id' => [$isBirthday ? 'nullable' : 'required', 'exists:pricing_packages,id'],
            'is_birthday_offer' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_birthday_offer' => $this->boolean('is_birthday_offer'),
        ]);
    }
}
