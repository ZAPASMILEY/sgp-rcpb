<?php

namespace Tests\Feature;

use App\Models\Direction;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_cannot_access_service_pages(): void
    {
        $this->get(route('admin.services.index'))->assertRedirect(route('login'));
        $this->get(route('admin.services.create'))->assertRedirect(route('login'));

        $service = Service::factory()->create();

        $this->get(route('admin.services.show', $service))->assertRedirect(route('login'));
        $this->get(route('admin.services.edit', $service))->assertRedirect(route('login'));
    }

    public function test_an_authenticated_admin_can_open_service_creation_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.services.create'));

        $response->assertOk();
        $response->assertSee('Nouveau service', false);
    }

    public function test_an_authenticated_admin_can_create_a_service(): void
    {
        $user = User::factory()->create();
        $direction = Direction::factory()->create(['nom' => 'Direction Financiere']);

        $response = $this->actingAs($user)->post(route('admin.services.store'), [
            'nom' => 'Service Tresorerie',
            'direction_id' => $direction->id,
            'chef_prenom' => 'Aicha',
            'chef_nom' => 'Tinga',
            'chef_email' => 'aicha.tinga@rcpb.bf',
            'chef_telephone' => '+22670000000',
        ]);

        $response->assertRedirect(route('admin.services.index'));

        $this->assertDatabaseHas('services', [
            'nom' => 'Service Tresorerie',
            'direction_id' => $direction->id,
            'chef_prenom' => 'Aicha',
            'chef_nom' => 'Tinga',
            'chef_email' => 'aicha.tinga@rcpb.bf',
            'chef_telephone' => '+22670000000',
        ]);
    }

    public function test_an_authenticated_admin_can_view_a_service(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'nom' => 'Service Audit Interne',
        ]);

        $response = $this->actingAs($user)->get(route('admin.services.show', $service));

        $response->assertOk();
        $response->assertSee('Service Audit Interne', false);
    }

    public function test_an_authenticated_admin_can_update_a_service(): void
    {
        $user = User::factory()->create();
        $direction = Direction::factory()->create(['nom' => 'Direction Operations']);
        $service = Service::factory()->create();

        $response = $this->actingAs($user)->put(route('admin.services.update', $service), [
            'nom' => 'Service Exploitation',
            'direction_id' => $direction->id,
            'chef_prenom' => 'Luc',
            'chef_nom' => 'Poda',
            'chef_email' => 'luc.poda@rcpb.bf',
            'chef_telephone' => '+22676000000',
        ]);

        $response->assertRedirect(route('admin.services.show', $service));

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'nom' => 'Service Exploitation',
            'direction_id' => $direction->id,
            'chef_prenom' => 'Luc',
            'chef_nom' => 'Poda',
            'chef_email' => 'luc.poda@rcpb.bf',
            'chef_telephone' => '+22676000000',
        ]);
    }

    public function test_an_authenticated_admin_can_delete_a_service(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.services.destroy', $service));

        $response->assertRedirect(route('admin.services.index'));
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_an_authenticated_admin_can_search_services(): void
    {
        $user = User::factory()->create();

        Service::factory()->create([
            'nom' => 'Service Marketing',
            'chef_nom' => 'Nikiema',
        ]);

        Service::factory()->create([
            'nom' => 'Service Juridique',
            'chef_nom' => 'Ouedraogo',
        ]);

        $response = $this->actingAs($user)->get(route('admin.services.index', [
            'search' => 'Marketing',
        ]));

        $response->assertOk();
        $response->assertSee('Service Marketing', false);
        $response->assertDontSee('Service Juridique', false);
    }

    public function test_an_authenticated_admin_can_filter_services_by_direction(): void
    {
        $user = User::factory()->create();
        $directionOne = Direction::factory()->create(['nom' => 'Direction Risques']);
        $directionTwo = Direction::factory()->create(['nom' => 'Direction Strategie']);

        Service::factory()->create([
            'nom' => 'Service Controle',
            'direction_id' => $directionOne->id,
        ]);

        Service::factory()->create([
            'nom' => 'Service Planification',
            'direction_id' => $directionTwo->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.services.index', [
            'direction_id' => $directionOne->id,
        ]));

        $response->assertOk();
        $response->assertSee('Service Controle', false);
        $response->assertDontSee('Service Planification', false);
    }

    public function test_service_index_is_paginated_for_authenticated_admin(): void
    {
        $user = User::factory()->create();

        Service::factory()->count(11)->create();

        $responsePageOne = $this->actingAs($user)->get(route('admin.services.index'));

        $responsePageOne->assertOk();
        $responsePageOne->assertViewHas('services', function ($paginator): bool {
            return $paginator->perPage() === 10
                && $paginator->total() === 11
                && $paginator->currentPage() === 1
                && $paginator->count() === 10;
        });

        $responsePageTwo = $this->actingAs($user)->get(route('admin.services.index', ['page' => 2]));

        $responsePageTwo->assertOk();
        $responsePageTwo->assertViewHas('services', function ($paginator): bool {
            return $paginator->currentPage() === 2 && $paginator->count() === 1;
        });
    }
}
