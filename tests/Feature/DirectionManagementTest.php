<?php

namespace Tests\Feature;

use App\Models\Direction;
use App\Models\Entite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_cannot_access_direction_pages(): void
    {
        $this->get(route('admin.directions.index'))->assertRedirect(route('login'));
        $this->get(route('admin.directions.create'))->assertRedirect(route('login'));

        $direction = Direction::factory()->create();

        $this->get(route('admin.directions.show', $direction))->assertRedirect(route('login'));
        $this->get(route('admin.directions.edit', $direction))->assertRedirect(route('login'));
    }

    public function test_an_authenticated_admin_can_open_direction_creation_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.directions.create'));

        $response->assertOk();
        $response->assertSee('Nouvelle direction', false);
    }

    public function test_an_authenticated_admin_can_create_a_direction(): void
    {
        $user = User::factory()->create();
        $entite = Entite::factory()->create(['nom' => 'RCPB Centre']);

        $response = $this->actingAs($user)->post(route('admin.directions.store'), [
            'nom' => 'Direction Financiere',
            'entite_id' => $entite->id,
            'directeur_nom' => 'Aicha Tinga',
            'directeur_email' => 'aicha.tinga@rcpb.bf',
            'secretariat_telephone' => '+22670000000',
        ]);

        $response->assertRedirect(route('admin.directions.index'));

        $this->assertDatabaseHas('directions', [
            'nom' => 'Direction Financiere',
            'entite_id' => $entite->id,
            'directeur_nom' => 'Aicha Tinga',
            'directeur_email' => 'aicha.tinga@rcpb.bf',
            'secretariat_telephone' => '+22670000000',
        ]);
    }

    public function test_an_authenticated_admin_can_view_a_direction(): void
    {
        $user = User::factory()->create();
        $direction = Direction::factory()->create([
            'nom' => 'Direction Informatique',
        ]);

        $response = $this->actingAs($user)->get(route('admin.directions.show', $direction));

        $response->assertOk();
        $response->assertSee('Direction Informatique', false);
    }

    public function test_an_authenticated_admin_can_update_a_direction(): void
    {
        $user = User::factory()->create();
        $entite = Entite::factory()->create(['nom' => 'RCPB Est']);
        $direction = Direction::factory()->create();

        $response = $this->actingAs($user)->put(route('admin.directions.update', $direction), [
            'nom' => 'Direction Audit',
            'entite_id' => $entite->id,
            'directeur_nom' => 'Luc Poda',
            'directeur_email' => 'luc.poda@rcpb.bf',
            'secretariat_telephone' => '+22676000000',
        ]);

        $response->assertRedirect(route('admin.directions.show', $direction));

        $this->assertDatabaseHas('directions', [
            'id' => $direction->id,
            'nom' => 'Direction Audit',
            'entite_id' => $entite->id,
            'directeur_nom' => 'Luc Poda',
            'directeur_email' => 'luc.poda@rcpb.bf',
            'secretariat_telephone' => '+22676000000',
        ]);
    }

    public function test_an_authenticated_admin_can_delete_a_direction(): void
    {
        $user = User::factory()->create();
        $direction = Direction::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.directions.destroy', $direction));

        $response->assertRedirect(route('admin.directions.index'));
        $this->assertDatabaseMissing('directions', ['id' => $direction->id]);
    }

    public function test_an_authenticated_admin_can_search_directions(): void
    {
        $user = User::factory()->create();

        Direction::factory()->create([
            'nom' => 'Direction Marketing',
            'directeur_nom' => 'Alpha Nikiema',
        ]);

        Direction::factory()->create([
            'nom' => 'Direction Juridique',
            'directeur_nom' => 'Brice Ouedraogo',
        ]);

        $response = $this->actingAs($user)->get(route('admin.directions.index', [
            'search' => 'Marketing',
        ]));

        $response->assertOk();
        $response->assertSee('Direction Marketing', false);
        $response->assertDontSee('Direction Juridique', false);
    }

    public function test_an_authenticated_admin_can_filter_directions_by_entite(): void
    {
        $user = User::factory()->create();
        $entiteOne = Entite::factory()->create(['nom' => 'RCPB Nord']);
        $entiteTwo = Entite::factory()->create(['nom' => 'RCPB Sud']);

        Direction::factory()->create([
            'nom' => 'Direction Risques',
            'entite_id' => $entiteOne->id,
        ]);

        Direction::factory()->create([
            'nom' => 'Direction Strategie',
            'entite_id' => $entiteTwo->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.directions.index', [
            'entite_id' => $entiteOne->id,
        ]));

        $response->assertOk();
        $response->assertSee('Direction Risques', false);
        $response->assertDontSee('Direction Strategie', false);
    }

    public function test_direction_index_is_paginated_for_authenticated_admin(): void
    {
        $user = User::factory()->create();

        Direction::factory()->count(11)->create();

        $responsePageOne = $this->actingAs($user)->get(route('admin.directions.index'));

        $responsePageOne->assertOk();
        $responsePageOne->assertViewHas('directions', function ($paginator): bool {
            return $paginator->perPage() === 10
                && $paginator->total() === 11
                && $paginator->currentPage() === 1
                && $paginator->count() === 10;
        });

        $responsePageTwo = $this->actingAs($user)->get(route('admin.directions.index', ['page' => 2]));

        $responsePageTwo->assertOk();
        $responsePageTwo->assertViewHas('directions', function ($paginator): bool {
            return $paginator->currentPage() === 2 && $paginator->count() === 1;
        });
    }
}
