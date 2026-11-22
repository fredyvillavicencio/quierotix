<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPurchaseResultPagesTest extends TestCase
{
    public function test_compra_completa_page_is_public_and_shows_success_message(): void
    {
        $response = $this->get('/compra_completa');

        $response->assertOk()
            ->assertSeeText('¡Compra completada!')
            ->assertSeeText('Pronto recibirás un email con tus tickets.');
    }

    public function test_compra_error_page_is_public_and_shows_error_message(): void
    {
        $response = $this->get('/compra_error');

        $response->assertOk()
            ->assertSeeText('No pudimos procesar tu compra')
            ->assertSeeText('Tuvimos un problema al procesar tu compra, intenta nuevamente.')
            ->assertSeeText('Volver');
    }
}
