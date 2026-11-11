<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ProviderDocument;
use App\Models\ServiceProvider;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Sellos públicos de un proveedor: qué se muestra, con qué fecha y cuándo vence.
 */
class TrustBadgesTest extends TestCase
{
    private function provider(array $attributes): ServiceProvider
    {
        return (new ServiceProvider())->forceFill($attributes);
    }

    public function test_without_documents_there_are_no_badges(): void
    {
        $provider = $this->provider([]);

        $this->assertSame([], $provider->trustBadges());
        $this->assertFalse($provider->isVerified());
    }

    public function test_each_badge_shows_its_review_date(): void
    {
        $date = Carbon::parse('2026-11-04 10:00');
        $provider = $this->provider([
            'identity_verified_at' => $date,
            'background_checked_at' => $date,
            'background_valid_until' => now()->addMonth(),
            'cv_reviewed_at' => $date,
        ]);

        $this->assertSame([
            ['label' => 'Identidad verificada', 'detail' => 'Cédula revisada el 04/11/2026'],
            ['label' => 'Sin antecedentes penales', 'detail' => 'Certificado oficial revisado el 04/11/2026'],
            ['label' => 'Experiencia revisada', 'detail' => 'Documentos revisados el 04/11/2026'],
        ], $provider->trustBadges());
        $this->assertTrue($provider->isVerified());
    }

    public function test_the_background_badge_disappears_when_it_expires(): void
    {
        $provider = $this->provider(['background_checked_at' => now()->subYear(), 'background_valid_until' => now()->subSecond()]);

        $this->assertNull($provider->badgeFor(ProviderDocument::BACKGROUND));
        $this->assertFalse($provider->isVerified());
    }

    public function test_a_reviewed_cv_alone_does_not_make_someone_verified(): void
    {
        $provider = $this->provider(['cv_reviewed_at' => now()]);

        $this->assertCount(1, $provider->trustBadges());
        $this->assertFalse($provider->isVerified());
    }

    public function test_every_document_type_explains_itself(): void
    {
        foreach (ProviderDocument::TYPES as $type => $meta) {
            foreach (['label', 'badge', 'why', 'how', 'consent', 'review'] as $key) {
                $this->assertNotEmpty($meta[$key] ?? null, "{$type}.{$key}");
            }
        }

        $this->assertStringContainsString('dato sensible', ProviderDocument::TYPES[ProviderDocument::BACKGROUND]['consent']);
        $this->assertStringContainsString('retirar', ProviderDocument::TYPES[ProviderDocument::BACKGROUND]['consent']);
    }

    public function test_document_status_labels(): void
    {
        $document = new ProviderDocument();

        foreach ([ProviderDocument::PENDING => 'En revisión', ProviderDocument::APPROVED => 'Aprobado', ProviderDocument::REJECTED => 'Sin sello'] as $status => $label) {
            $document->status = $status;
            $this->assertSame($label, $document->statusLabel());
        }
    }
}
