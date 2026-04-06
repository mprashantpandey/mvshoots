<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPasswordResetPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_forgot_password_page_is_accessible(): void
    {
        $this->get(route('admin.password.request'))
            ->assertOk();
    }

    public function test_admin_reset_password_page_is_accessible(): void
    {
        $this->get(route('admin.password.reset', ['token' => 'sample-token', 'email' => 'admin@vmshoot.test']))
            ->assertOk();
    }
}
