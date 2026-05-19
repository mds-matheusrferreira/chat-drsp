<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_documents_area_requires_login(): void
    {
        $this->get(route('documents.index'))
            ->assertRedirect(route('documents.login'));
    }

    public function test_admin_can_login_to_documents_area(): void
    {
        config([
            'knowledge.document_admin_username' => 'admin',
            'knowledge.document_admin_password' => 'drsp',
        ]);

        $this->post(route('documents.login.store'), [
            'username' => 'admin',
            'password' => 'drsp',
        ])
            ->assertRedirect(route('documents.index'))
            ->assertSessionHas('documents_admin_authenticated', true);
    }

    public function test_invalid_credentials_do_not_authenticate_documents_area(): void
    {
        config([
            'knowledge.document_admin_username' => 'admin',
            'knowledge.document_admin_password' => 'drsp',
        ]);

        $this->from(route('documents.login'))
            ->post(route('documents.login.store'), [
                'username' => 'admin',
                'password' => 'errada',
            ])
            ->assertRedirect(route('documents.login'))
            ->assertSessionHasErrors('login')
            ->assertSessionMissing('documents_admin_authenticated');
    }
}
