<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProviderDocument;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Support\Notice;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Único camino para cambiar un sello. Los documentos son datos personales (el certificado de
 * antecedentes es dato sensible, LOPDP art. 4 y 26): viven en el disco privado y se borran apenas
 * se revisan. Lo único que se conserva es el resultado, la fecha y quién revisó.
 */
final class ProviderVerification
{
    public const DISK = 'receipts';

    public function submit(ServiceProvider $provider, string $type, UploadedFile $file): ProviderDocument
    {
        $this->discardPending($provider, $type);

        $path = $file->storeAs('verificaciones', $provider->id . '_' . $type . '_' . Str::random(16) . '.' . $file->extension(), self::DISK);

        $document = ProviderDocument::create([
            'service_provider_id' => $provider->id,
            'type' => $type,
            'path' => $path,
            'status' => ProviderDocument::PENDING,
            'consent_at' => now(),
        ]);

        // El admin no entra todos los días; sin este aviso el documento queda esperando.
        if ($address = config('marketplace.lead_notification_address')) {
            Notice::send((string) $address, 'Un proveedor espera verificación', [
                $provider->user?->name . ' subió: ' . $document->label() . '.',
                'Revísalo lo antes posible: el archivo se borra apenas lo revisas.',
            ], 'Revisar documentos', route('admin.verifications'));
        }

        return $document;
    }

    public function approve(ProviderDocument $document, User $admin): void
    {
        if ($document->status !== ProviderDocument::PENDING) {
            return;
        }

        DB::transaction(function () use ($document, $admin): void {
            $provider = $document->provider;

            match ($document->type) {
                ProviderDocument::IDENTITY => $provider->identity_verified_at = now(),
                ProviderDocument::CV => $provider->cv_reviewed_at = now(),
                ProviderDocument::BACKGROUND => [
                    $provider->background_checked_at = now(),
                    $provider->background_valid_until = now()->addDays((int) config('marketplace.verification.background_valid_days')),
                ],
            };
            $provider->save();

            $this->close($document, ProviderDocument::APPROVED, $admin);
        });

        $this->deleteFile($document);

        $this->tellProvider($document, 'Tienes un sello nuevo en Malakplace', [
            'Revisamos tu documento y ya aparece en tu perfil el sello «' . ProviderDocument::TYPES[$document->type]['badge'] . '».',
            'El archivo ya se borró de nuestro sistema.',
        ]);
    }

    public function reject(ProviderDocument $document, User $admin, string $reason): void
    {
        if ($document->status !== ProviderDocument::PENDING) {
            return;
        }

        $document->rejection_reason = $reason;
        $this->close($document, ProviderDocument::REJECTED, $admin);
        $this->deleteFile($document);

        $this->tellProvider($document, 'No pudimos darte el sello en Malakplace', [
            'Revisamos tu documento (' . $document->label() . ') y no pudimos darte el sello.',
            'Motivo: ' . $reason,
            'Puedes seguir usando Malakplace con normalidad. Si quieres, sube otro documento desde tu panel.',
        ]);
    }

    /**
     * El proveedor retira su permiso (LOPDP art. 8) o el admin quita un sello: se borra todo lo de ese tipo.
     */
    public function withdraw(ServiceProvider $provider, string $type): void
    {
        DB::transaction(function () use ($provider, $type): void {
            match ($type) {
                ProviderDocument::IDENTITY => $provider->identity_verified_at = null,
                ProviderDocument::CV => $provider->cv_reviewed_at = null,
                ProviderDocument::BACKGROUND => [
                    $provider->background_checked_at = null,
                    $provider->background_valid_until = null,
                ],
            };
            $provider->save();
        });

        $documents = $provider->documents()->where('type', $type)->get();
        $documents->each(fn (ProviderDocument $document) => $this->deleteFile($document));
        $provider->documents()->where('type', $type)->delete();
    }

    /**
     * Antes de eliminar una cuenta: la base borra las filas en cascada, pero los archivos no.
     */
    public function forget(ServiceProvider $provider): void
    {
        $provider->documents()->get()->each(fn (ProviderDocument $document) => $this->deleteFile($document));
    }

    private function discardPending(ServiceProvider $provider, string $type): void
    {
        $provider->documents()->where('type', $type)->where('status', ProviderDocument::PENDING)->get()
            ->each(function (ProviderDocument $document): void {
                $this->deleteFile($document);
                $document->delete();
            });
    }

    private function close(ProviderDocument $document, string $status, User $admin): void
    {
        $document->status = $status;
        $document->reviewed_by = $admin->id;
        $document->reviewed_at = now();
        $document->save();
    }

    private function deleteFile(ProviderDocument $document): void
    {
        if ($document->path === null) {
            return;
        }

        try {
            Storage::disk(self::DISK)->delete($document->path);
            $document->path = null;

            if ($document->exists) {
                $document->save();
            }
        } catch (Throwable $exception) {
            // La ruta se conserva: el archivo sigue ahí y hay que poder encontrarlo.
            report($exception);
        }
    }

    /**
     * @param  list<string>  $lines
     */
    private function tellProvider(ProviderDocument $document, string $subject, array $lines): void
    {
        if ($email = $document->provider->user?->email) {
            Notice::send($email, $subject, $lines, 'Ir a mi verificación', route('sprovider.verification'));
        }
    }
}
