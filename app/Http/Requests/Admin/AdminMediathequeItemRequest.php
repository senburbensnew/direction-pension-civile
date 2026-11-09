<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminMediathequeItemRequest extends FormRequest
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
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:image,video,audio,document',
            'file' => 'nullable|file|max:51200',
            'url' => 'nullable|url|max:500',
            'order_column' => 'nullable|integer|min:0',
            'published' => 'boolean',
        ];
    }
}
