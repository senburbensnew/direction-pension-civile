<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carousel extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'image', 'link', 'status', 'order',
        'overlay_position', 'cta_label', 'text_size',
        'text_color', 'text_styles', 'font_family',
    ];

    protected $casts = [
        'text_styles' => 'array',
        'status'      => 'boolean',
    ];

    public const TEXT_SIZES = [
        'sm' => 'Petit',
        'md' => 'Moyen',
        'lg' => 'Grand',
        'xl' => 'Très grand',
    ];

    public const POSITIONS = [
        'bottom-left'   => 'Bas gauche',
        'bottom-center' => 'Bas centre',
        'bottom-right'  => 'Bas droite',
        'center'        => 'Centre',
        'top-left'      => 'Haut gauche',
        'top-center'    => 'Haut centre',
    ];

    public const TEXT_STYLES = [
        'bold'      => 'Gras',
        'italic'    => 'Italique',
        'underline' => 'Souligné',
        'uppercase' => 'Majuscules',
    ];

    public const FONT_FAMILIES = [
        'sans'      => 'Sans Serif',
        'serif'     => 'Serif',
        'playfair'  => 'Playfair Display',
        'oswald'    => 'Oswald',
        'condensed' => 'Condensé',
    ];

    public const FONT_CSS = [
        'sans'      => "system-ui, -apple-system, sans-serif",
        'serif'     => "Georgia, 'Times New Roman', serif",
        'playfair'  => "'Playfair Display', Georgia, serif",
        'oswald'    => "'Oswald', 'Arial Narrow', sans-serif",
        'condensed' => "'Arial Narrow', Impact, sans-serif",
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    public function fontFamilyCss(): string
    {
        return self::FONT_CSS[$this->font_family ?? 'sans'] ?? self::FONT_CSS['sans'];
    }

    public function textStyleClasses(): string
    {
        $styles = $this->text_styles ?? [];
        $classes = [];
        if (in_array('bold', $styles))      $classes[] = 'font-bold';
        if (in_array('italic', $styles))    $classes[] = 'italic';
        if (in_array('underline', $styles)) $classes[] = 'underline';
        if (in_array('uppercase', $styles)) $classes[] = 'uppercase tracking-wide';
        return implode(' ', $classes);
    }

    /**
     * Returns the correct public URL regardless of whether the image
     * is a static asset (images/...) or an uploaded file (storage/...).
     */
    public function imageUrl(): string
    {
        if (str_starts_with($this->image, 'images/')) {
            return asset($this->image);
        }

        return asset('storage/' . $this->image);
    }
}
