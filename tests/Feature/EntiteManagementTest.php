<?php

namespace Tests\Feature;

use App\Models\Entite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntiteManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_cannot_access_entite_pages(): void
    {
        $this->get(route('admin.entites.index'))->assertRedirect(route('login'));
        $this->get(route('admin.entites.create'))->assertRedirect(route('login'));

        $entite = Entite::factory()->create();

        $this->get(route('admin.entites.show', $entite))->assertRedirect(route('login'));
        $this->get(route('admin.entites.edit', $entite))->assertRedirect(route('login'));
    }

    public function test_an_authenticated_admin_can_open_entite_creation_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.entites.create'));

        $response->assertOk();
        $response->assertSee('Nouvelle entite', false);
    }

    public function test_an_authenticated_admin_can_create_an_entite(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.entites.store'), [
            'nom' => 'RCPB Centre',
            'ville' => 'Ouagadougou',
            'directrice_generale_prenom' => 'Aminata',
            'directrice_generale_nom' => 'Traore',
            'directrice_generale_email' => 'aminata.traore@rcpb.bf',
            'pca_prenom' => 'Awa',
            'pca_nom' => 'Kabore',
            'pca_email' => 'awa.kabore@rcpb.bf',
            'secretariat_telephone' => '+22670000000',
        ]);

        $response->assertRedirect(route('admin.entites.index'));

        $this->assertDatabaseHas('entites', [
            'nom' => 'RCPB Centre',
            'ville' => 'Ouagadougou',
            'directrice_generale_nom' => 'Traore',
            'pca_nom' => 'Kabore',
        ]);

        $entite = Entite::query()->first();

        $this->assertNotNull($entite);
        $this->assertSame('Aminata', $entite?->directrice_generale_prenom);
        $this->assertSame('aminata.traore@rcpb.bf', $entite?->directrice_generale_email);
        $this->assertSame('Awa', $entite?->pca_prenom);
        $this->assertSame('awa.kabore@rcpb.bf', $entite?->pca_email);
        $this->assertSame('+22670000000', $entite?->secretariat_telephone);
    }

    public function test_an_authenticated_admin_can_view_an_entite(): void
    {
        $user = User::factory()->create();
        $entite = Entite::factory()->create([
            'nom' => 'RCPB Ouest',
            'ville' => 'Bobo-Dioulasso',
        ]);

        $response = $this->actingAs($user)->get(route('admin.entites.show', $entite));

        $response->assertOk();
        $response->assertSee('RCPB Ouest', false);
        $response->assertSee('Bobo-Dioulasso', false);
    }

    public function test_an_authenticated_admin_can_update_an_entite(): void
    {
        $user = User::factory()->create();
        $entite = Entite::factory()->create([
            'nom' => 'RCPB Sud',
            'ville' => 'Banfora',
        ]);

        $response = $this->actingAs($user)->put(route('admin.entites.update', $entite), [
            'nom' => 'RCPB Sud Est',
            'ville' => 'Tenkodogo',
            'directrice_generale_prenom' => 'Nadia',
            'directrice_generale_nom' => 'Jean',
            'directrice_generale_email' => 'nadia.jean@rcpb.bf',
            'pca_prenom' => 'Oumar',
            'pca_nom' => 'Ouedraogo',
            'pca_email' => 'oumar.ouedraogo@rcpb.bf',
            'secretariat_telephone' => '+22676000000',
        ]);

        $response->assertRedirect(route('admin.entites.show', $entite));

        $this->assertDatabaseHas('entites', [
            'id' => $entite->id,
            'nom' => 'RCPB Sud Est',
            'ville' => 'Tenkodogo',
            'directrice_generale_nom' => 'Jean',
            'pca_nom' => 'Ouedraogo',
            'secretariat_telephone' => '+22676000000',
        ]);
    }

    public function test_an_authenticated_admin_can_delete_an_entite(): void
    {
        $user = User::factory()->create();
        $entite = Entite::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.entites.destroy', $entite));

        $response->assertRedirect(route('admin.entites.index'));
        $this->assertDatabaseMissing('entites', [
            'id' => $entite->id,
        ]);
    }

    public function test_an_authenticated_admin_can_search_entites(): void
    {
        $user = User::factory()->create();

        Entite::factory()->create([
            'nom' => 'RCPB Plateau Central',
            'ville' => 'Ziniare',
            'directrice_generale_prenom' => 'Idrissa',
            'directrice_generale_nom' => 'Kiemde',
            'pca_prenom' => 'Mariam',
            'pca_nom' => 'Bado',
        ]);

        Entite::factory()->create([
            'nom' => 'RCPB Cascades',
            'ville' => 'Banfora',
            'directrice_generale_prenom' => 'Paul',
            'directrice_generale_nom' => 'Kinda',
            'pca_prenom' => 'Rose',
            'pca_nom' => 'Zongo',
        ]);

        $response = $this->actingAs($user)->get(route('admin.entites.index', [
            'search' => 'Plateau',
        ]));

        $response->assertOk();
        $response->assertSee('RCPB Plateau Central', false);
        $response->assertDontSee('RCPB Cascades', false);
    }

    public function test_an_authenticated_admin_can_filter_entites_by_city(): void
    {
        $user = User::factory()->create();

        Entite::factory()->create([
            'nom' => 'RCPB Nord',
            'ville' => 'Kaya',
        ]);

        Entite::factory()->create([
            'nom' => 'RCPB Est',
            'ville' => 'Fada N\'Gourma',
        ]);

        $response = $this->actingAs($user)->get(route('admin.entites.index', [
            'ville' => 'Kaya',
        ]));

        $response->assertOk();
        $response->assertSee('RCPB Nord', false);
        $response->assertDontSee('RCPB Est', false);
    }

    public function test_entite_index_is_paginated_for_authenticated_admin(): void
    {
        $user = User::factory()->create();

        Entite::factory()->count(11)->create();

        $responsePageOne = $this->actingAs($user)->get(route('admin.entites.index'));

        $responsePageOne->assertOk();
        $responsePageOne->assertViewHas('entites', function ($paginator): bool {
            return $paginator->perPage() === 10
                && $paginator->total() === 11
                && $paginator->currentPage() === 1
                && $paginator->count() === 10;
        });

        $responsePageTwo = $this->actingAs($user)->get(route('admin.entites.index', ['page' => 2]));

        $responsePageTwo->assertOk();
        $responsePageTwo->assertViewHas('entites', function ($paginator): bool {
            return $paginator->currentPage() === 2 && $paginator->count() === 1;
        });
    }
}
