<?php

namespace Tests\Feature;

use Tests\TestCase;

class LayoutOptimizationTest extends TestCase
{
    /** @test */
    public function login_page_returns_200()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /** @test */
    public function login_page_does_not_load_bootstrap_cdn()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertDontSee('maxcdn.bootstrapcdn.com', false);
    }

    /** @test */
    public function login_page_does_not_load_jquery()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertDontSee('jquery', false);
    }
}
