<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    // Card payment started in Payphone but not confirmed yet (the user may abandon it).
    public const INITIATED = 'initiated';
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    public const METHOD_TRANSFER = 'transfer';
    public const METHOD_CASH = 'cash';
    // No longer offered (only transfer and cash for now); kept so old records still get a label.
    public const METHOD_DEUNA = 'deuna';
    // Card payments through Payphone, disabled unless PAYPHONE_ENABLED=true.
    public const METHOD_PAYPHONE = 'payphone';

    /** Methods providers can choose on their plan page. */
    public const MANUAL_METHODS = [self::METHOD_TRANSFER, self::METHOD_CASH];

    public const STATUS_LABELS = [
        self::INITIATED => 'Iniciado (sin confirmar)',
        self::PENDING => 'En revisión',
        self::APPROVED => 'Aprobado',
        self::REJECTED => 'Rechazado',
    ];

    public const METHOD_LABELS = [
        self::METHOD_TRANSFER => 'Transferencia bancaria',
        self::METHOD_CASH => 'Efectivo',
        self::METHOD_DEUNA => 'DeUna',
        self::METHOD_PAYPHONE => 'Tarjeta (Payphone)',
    ];

    protected $fillable = [
        'user_id',
        'method',
        'amount',
        'periods',
        'status',
        'reference',
        'receipt_path',
        'client_transaction_id',
        'gateway_transaction_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'periods' => 'integer',
        'reviewed_at' => 'datetime',
        'paid_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function methodLabel(): string
    {
        return self::METHOD_LABELS[$this->method] ?? $this->method;
    }
}
