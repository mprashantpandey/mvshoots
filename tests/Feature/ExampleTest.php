<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_root_route_displays_the_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Landing')
                ->where('appName', 'VM Shoot')
            );
    }
}
