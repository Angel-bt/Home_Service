<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\WhatsApp;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WhatsAppTest extends TestCase
{
    public static function numbers(): array
    {
        return [
            'celular local' => ['0991234567', '593991234567'],
            'con espacios y guiones' => ['099-123 4567', '593991234567'],
            'con +593' => ['+593 99 123 4567', '593991234567'],
            'con 00593' => ['00593991234567', '593991234567'],
            'sin el cero inicial' => ['991234567', '593991234567'],
            'ya con código de país' => ['593991234567', '593991234567'],
            'fijo de Loja' => ['072123456', '59372123456'],
        ];
    }

    #[DataProvider('numbers')]
    public function test_ecuadorian_numbers_are_normalised(string $input, string $expected): void
    {
        $this->assertSame($expected, WhatsApp::normalize($input));
    }

    public static function invalid(): array
    {
        return ['vacío' => [''], 'nulo' => [null], 'letras' => ['abc'], 'muy corto' => ['12'], 'incompleto' => ['09912']];
    }

    #[DataProvider('invalid')]
    public function test_numbers_that_cannot_work_are_rejected(?string $input): void
    {
        $this->assertNull(WhatsApp::normalize($input));
        $this->assertNull(WhatsApp::link($input, 'Hola'));
    }

    public function test_links_encode_the_message(): void
    {
        $this->assertSame('https://wa.me/593991234567', WhatsApp::link('0991234567'));
        $this->assertSame('https://wa.me/593991234567?text=Hola%2C%20%C2%BFatiende%20hoy%3F', WhatsApp::link('0991234567', 'Hola, ¿atiende hoy?'));
    }
}
