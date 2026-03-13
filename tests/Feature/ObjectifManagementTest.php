<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Objectif;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObjectifManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_cannot_access_objectif_pages(): void
    {
        $this->get(route('admin.objectifs.index'))->assertRedirect(route('login'));
        $this->get(route('admin.objectifs.create'))->assertRedirect(route('login'));

        $objectif = Objectif::factory()->create();

        $this->get(route('admin.objectifs.show', $objectif))->assertRedirect(route('login'));
        $this->get(route('admin.objectifs.edit', $objectif))->assertRedirect(route('login'));
    }

    public function test_an_authenticated_admin_can_create_an_objectif_assigned_to_a_service_with_auto_date(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['nom' => 'Service Evaluation']);

        $response = $this->actingAs($user)->post(route('admin.objectifs.store'), [
            'assignable_type' => 'service',
            'assignable_id' => $service->id,
            'date_echeance' => now()->addDays(10)->toDateString(),
            'commentaire' => 'Atteindre 95% de realisation du plan de performance trimestriel.',
        ]);

        $response->assertRedirect(route('admin.objectifs.index'));

        $this->assertDatabaseHas('objectifs', [
            'assignable_type' => Service::class,
            'assignable_id' => $service->id,
            'date' => now()->toDateString(),
            'commentaire' => 'Atteindre 95% de realisation du plan de performance trimestriel.',
            'avancement_percentage' => 0,
        ]);
    }

    public function test_an_authenticated_admin_can_create_an_objectif_assigned_to_an_agent(): void
    {
        $user = User::factory()->create();
        $agent = Agent::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.objectifs.store'), [
            'assignable_type' => 'agent',
            'assignable_id' => $agent->id,
            'date_echeance' => now()->addDays(30)->toDateString(),
            'commentaire' => 'Finaliser les indicateurs de suivi mensuel avant la date limite.',
        ]);

        $response->assertRedirect(route('admin.objectifs.index'));

        $this->assertDatabaseHas('objectifs', [
            'assignable_type' => Agent::class,
            'assignable_id' => $agent->id,
        ]);
    }

    public function test_an_authenticated_admin_can_update_an_objectif(): void
    {
        $user = User::factory()->create();
        $entite = Entite::factory()->create(['nom' => 'RCPB Siege']);
        $direction = Direction::factory()->create(['nom' => 'Direction Controle']);
        $objectif = Objectif::factory()->create([
            'assignable_type' => Entite::class,
            'assignable_id' => $entite->id,
            'commentaire' => 'Ancien commentaire',
        ]);

        $response = $this->actingAs($user)->put(route('admin.objectifs.update', $objectif), [
            'assignable_type' => 'direction',
            'assignable_id' => $direction->id,
            'date_echeance' => now()->addDays(20)->toDateString(),
            'commentaire' => 'Nouveau commentaire de pilotage pour la direction.',
        ]);

        $response->assertRedirect(route('admin.objectifs.show', $objectif));

        $this->assertDatabaseHas('objectifs', [
            'id' => $objectif->id,
            'assignable_type' => Direction::class,
            'assignable_id' => $direction->id,
            'commentaire' => 'Nouveau commentaire de pilotage pour la direction.',
        ]);
    }

    public function test_an_authenticated_admin_can_delete_an_objectif(): void
    {
        $user = User::factory()->create();
        $objectif = Objectif::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.objectifs.destroy', $objectif));

        $response->assertRedirect(route('admin.objectifs.index'));
        $this->assertDatabaseMissing('objectifs', ['id' => $objectif->id]);
    }

    public function test_an_authenticated_admin_can_adjust_objectif_progress_from_index(): void
    {
        $user = User::factory()->create();
        $objectif = Objectif::factory()->create([
            'avancement_percentage' => 40,
        ]);

        $increase = $this->actingAs($user)->post(route('admin.objectifs.progress', $objectif), [
            'direction' => 'up',
        ]);

        $increase->assertRedirect(route('admin.objectifs.index'));
        $this->assertDatabaseHas('objectifs', [
            'id' => $objectif->id,
            'avancement_percentage' => 50,
        ]);

        $decrease = $this->actingAs($user)->post(route('admin.objectifs.progress', $objectif), [
            'direction' => 'down',
        ]);

        $decrease->assertRedirect(route('admin.objectifs.index'));
        $this->assertDatabaseHas('objectifs', [
            'id' => $objectif->id,
            'avancement_percentage' => 40,
        ]);
    }

    public function test_an_authenticated_admin_cannot_adjust_progress_after_deadline(): void
    {
        $user = User::factory()->create();
        $objectif = Objectif::factory()->create([
            'date_echeance' => now()->subDay()->toDateString(),
            'avancement_percentage' => 40,
        ]);

        $response = $this->actingAs($user)->post(route('admin.objectifs.progress', $objectif), [
            'direction' => 'up',
        ]);

        $response->assertRedirect(route('admin.objectifs.index'));
        $this->assertDatabaseHas('objectifs', [
            'id' => $objectif->id,
            'avancement_percentage' => 40,
        ]);
    }

    public function test_an_authenticated_admin_can_search_objectifs(): void
    {
        $user = User::factory()->create();

        Objectif::factory()->create([
            'commentaire' => 'Objectif prioritaire sur la reduction des delais de traitement.',
        ]);

        Objectif::factory()->create([
            'commentaire' => 'Objectif secondaire pour la documentation interne.',
        ]);

        $response = $this->actingAs($user)->get(route('admin.objectifs.index', [
            'search' => 'reduction des delais',
        ]));

        $response->assertOk();
        $response->assertSee('reduction des delais', false);
        $response->assertDontSee('documentation interne', false);
    }

    public function test_objectif_index_is_paginated_for_authenticated_admin(): void
    {
        $user = User::factory()->create();

        Objectif::factory()->count(11)->create();

        $responsePageOne = $this->actingAs($user)->get(route('admin.objectifs.index'));

        $responsePageOne->assertOk();
        $responsePageOne->assertViewHas('objectifs', function ($paginator): bool {
            return $paginator->perPage() === 10
                && $paginator->total() === 11
                && $paginator->currentPage() === 1
                && $paginator->count() === 10;
        });

        $responsePageTwo = $this->actingAs($user)->get(route('admin.objectifs.index', ['page' => 2]));

        $responsePageTwo->assertOk();
        $responsePageTwo->assertViewHas('objectifs', function ($paginator): bool {
            return $paginator->currentPage() === 2 && $paginator->count() === 1;
        });
    }
}