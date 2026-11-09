<?php

namespace App\Http\Requests\Admin\Concerns;

trait ValidatesCarouselForm
{
    /**
     * @return array<string, mixed>
     */
    protected function carouselAttributeRules(string $imageRule): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => [$imageRule, 'image', 'mimes:jpeg,png,jpg,gif', 'max:4096'],
            'link' => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/\S+|\/[^\s]*)$/i'],
            'cta_label' => ['nullable', 'string', 'max:60'],
            'overlay_position' => ['nullable', 'string'],
            'text_size' => ['nullable', 'string', 'in:sm,md,lg,xl'],
            'text_color' => ['nullable', 'string', 'max:20'],
            'text_styles' => ['nullable', 'array'],
            'text_styles.*' => ['in:bold,italic,underline,uppercase'],
            'font_family' => ['nullable', 'string', 'in:sans,serif,playfair,oswald,condensed'],
            'status' => ['boolean'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
