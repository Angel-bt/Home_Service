<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Una acción en el sitio, para analizar la actividad. Se escribe con App\Support\Activity.
 * No guarda IP, navegador ni mensajes.
 */
class ActivityEvent extends Model
{
    public const SEARCH = 'search';
    public const CATEGORY_VIEW = 'category_view';
    public const SERVICE_VIEW = 'service_view';
    public const WHATSAPP_CLICK = 'whatsapp_click';
    public const REQUEST_CREATED = 'request_created';
    public const REQUEST_COMPLETED = 'request_completed';
    public const REVIEW_CREATED = 'review_created';
    public const SIGNUP = 'signup';
    public const LOGIN = 'login';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
