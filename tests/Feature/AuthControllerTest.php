<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function test_login_page_loads_without_external_assets(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('cdnjs.cloudflare.com', false)
            ->assertDontSee('images.unsplash.com', false);
    }
}
