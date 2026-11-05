<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Documento que un proveedor entrega por voluntad propia para obtener un sello.
 * El archivo vive en el disco privado solo hasta que se revisa (ver ProviderVerification).
 */
class ProviderDocument extends Model
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    public const IDENTITY = 'identity';
    public const BACKGROUND = 'background';
    public const CV = 'cv';

    public const TYPES = [
        self::IDENTITY => [
            'label' => 'Tu cédula',
            'badge' => 'Identidad verificada',
            'why' => 'Los clientes saben que eres quien dices ser.',
            'how' => 'Sube una foto clara de la parte de adelante de tu cédula.',
            'consent' => 'Autorizo a Malakplace a revisar mi cédula para verificar mi identidad y a publicar el sello «Identidad verificada» en mi perfil. La cédula no se publica y se borra después de revisarla.',
            'review' => 'Revisa que la foto se lea bien y que el nombre sea el mismo de la cuenta.',
        ],
        self::BACKGROUND => [
            'label' => 'Certificado de antecedentes penales',
            'badge' => 'Sin antecedentes penales',
            'why' => 'Es lo que más tranquilidad da a quien te abre la puerta de su casa.',
            'how' => 'Sácalo gratis por internet en la página del Ministerio del Interior, con tu número de cédula, y súbelo aquí. Vale 3 meses: súbelo recién sacado.',
            'consent' => 'Autorizo de forma expresa a Malakplace a revisar mi certificado de antecedentes penales, que es un dato sensible, con el único fin de dar el sello «Sin antecedentes penales» y publicarlo en mi perfil con su fecha. El certificado no se publica y se borra después de revisarlo. Es voluntario y puedo retirar este permiso cuando quiera.',
            'review' => 'Revisa que tenga menos de 3 meses, que el nombre sea el mismo de la cuenta y que diga que no registra antecedentes. Si registra alguno, recházalo sin dar detalles: el proveedor sigue en la plataforma, solo que sin este sello.',
        ],
        self::CV => [
            'label' => 'Hoja de vida o certificados de cursos',
            'badge' => 'Experiencia revisada',
            'why' => 'Muestra que tu experiencia es real. Solo nosotros vemos el archivo.',
            'how' => 'Sube tu hoja de vida o un certificado de un curso, por ejemplo del SECAP.',
            'consent' => 'Autorizo a Malakplace a revisar este documento para dar el sello «Experiencia revisada» y publicarlo en mi perfil. El documento no se publica y se borra después de revisarlo.',
            'review' => 'Revisa que el nombre sea el mismo de la cuenta y que la experiencia tenga que ver con su oficio.',
        ],
    ];

    protected $fillable = ['service_provider_id', 'type', 'path', 'status', 'consent_at'];

    protected $casts = [
        'consent_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function label(): string
    {
        return self::TYPES[$this->type]['label'] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::PENDING => 'En revisión',
            self::APPROVED => 'Aprobado',
            self::REJECTED => 'Sin sello',
            default => $this->status,
        };
    }
}
