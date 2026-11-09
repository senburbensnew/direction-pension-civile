<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesCarouselForm;
use Illuminate\Foundation\Http\FormRequest;

class StoreCarouselRequest extends FormRequest
{
    use ValidatesCarouselForm;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->carouselAttributeRules('required');
    }
}
