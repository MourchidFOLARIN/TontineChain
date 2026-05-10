<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test que l'API est accessible.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // On teste l'endpoint API santé au lieu de la page web (qui nécessite Vite)
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200);
    }
}
