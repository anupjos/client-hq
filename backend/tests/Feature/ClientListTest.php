<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_clients(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->client()->count(2)->create();
        User::factory()->admin()->count(1)->create();

        Sanctum::actingAs($admin);

        $this->getJson('/api/clients')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonStructure([['id', 'name', 'email']]);
    }

    public function test_client_cannot_list_clients(): void
    {
        $client = User::factory()->client()->create();
        Sanctum::actingAs($client);

        $this->getJson('/api/clients')->assertForbidden();
    }
}
