<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MediathequeItemRequest extends FormRequest
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
            'title' => 'required|string|max:300',
            'description' => 'nullable|string',
            'type' => 'required|string|in:image,video,audio,document',
            'file' => 'nullable|file|max:51200',
            'url' => 'nullable|url|max:500',
            'order_column' => 'nullable|integer|min:0',
            'published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ];
    }
}
