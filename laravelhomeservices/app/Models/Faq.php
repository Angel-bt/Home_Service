<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Content;
use Illuminate\Database\Eloquent\Model;

/**
 * Pregunta frecuente que la administración edita en /admin/faq.
 * Las respuestas pueden llevar datos que cambian solos: ver Content::PLACEHOLDERS.
 */
class Faq extends Model
{
    public const GROUPS = ['Para clientes', 'Para proveedores', 'General'];

    public const PLACEHOLDERS = Content::PLACEHOLDERS;

    protected $fillable = ['group', 'question', 'answer', 'position'];

    protected $casts = ['position' => 'integer'];

    public function renderedAnswer(): string
    {
        return Content::fill($this->answer);
    }
}
