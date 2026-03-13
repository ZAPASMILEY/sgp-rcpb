<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Evaluation;
use App\Models\Objectif;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_cannot_access_evaluation_pages(): void
    {
        $this->get(route('admin.evaluations.index'))->assertRedirect(route('login'));
        $this->get(route('admin.evaluations.create'))->assertRedirect(route('login'));

        $evaluation = Evaluation::factory()->create();

        $this->get(route('admin.evaluations.show', $evaluation))->assertRedirect(route('login'));
    }

    public function test_an_authenticated_admin_can_create_an_evaluation_with_auto_note_from_objectifs(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $agent = Agent::factory()->create(['service_id' => $service->id]);

        Objectif::factory()->create([
            'assignable_type' => Agent::class,
            'assignable_id' => $agent->id,
            'date' => '2026-03-10',
            'avancement_percentage' => 60,
        ]);

        Objectif::factory()->create([
            'assignable_type' => Agent::class,
            'assignable_id' => $agent->id,
            'date' => '2026-03-12',
            'avancement_percentage' => 80,
        ]);

        $response = $this->actingAs($user)->post(route('admin.evaluations.store'), [
            'evaluable_type' => 'agent',
            'evaluable_id' => $agent->id,
            'date_debut' => '2026-03-01',
            'date_fin' => '2026-03-31',
            'commentaire' => 'Bonne evolution globale',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('evaluations', [
            'evaluable_type' => Agent::class,
            'evaluable_id' => $agent->id,
            'evaluateur_id' => $user->id,
            'note_objectifs' => 70,
            'note_finale' => 70,
            'statut' => 'brouillon',
        ]);
    }

    public function test_an_authenticated_admin_can_submit_then_validate_an_evaluation(): void
    {
        $user = User::factory()->create();
        $agent = Agent::factory()->create();
        $evaluation = Evaluation::factory()->create([
            'evaluable_type' => Agent::class,
            'evaluable_id' => $agent->id,
            'evaluateur_id' => $user->id,
            'date_debut' => '2026-03-01',
            'date_fin' => '2026-03-31',
            'statut' => 'brouillon',
        ]);

        $objectifInPeriod = Objectif::factory()->create([
            'assignable_type' => Agent::class,
            'assignable_id' => $agent->id,
            'date' => '2026-03-10',
            'avancement_percentage' => 40,
        ]);

        $objectifOutOfPeriod = Objectif::factory()->create([
            'assignable_type' => Agent::class,
            'assignable_id' => $agent->id,
            'date' => '2026-04-10',
            'avancement_percentage' => 30,
        ]);

        $this->actingAs($user)
            ->post(route('admin.evaluations.submit', $evaluation))
            ->assertRedirect(route('admin.evaluations.show', $evaluation));

        $this->assertDatabaseHas('evaluations', [
            'id' => $evaluation->id,
            'statut' => 'soumis',
        ]);

        $this->actingAs($user)
            ->post(route('admin.evaluations.approve', $evaluation))
            ->assertRedirect(route('admin.evaluations.show', $evaluation));

        $this->assertDatabaseHas('evaluations', [
            'id' => $evaluation->id,
            'statut' => 'valide',
        ]);

        $this->assertDatabaseHas('objectifs', [
            'id' => $objectifInPeriod->id,
            'avancement_percentage' => 100,
        ]);

        $this->assertDatabaseHas('objectifs', [
            'id' => $objectifOutOfPeriod->id,
            'avancement_percentage' => 30,
        ]);
    }

    public function test_an_authenticated_admin_cannot_delete_a_validated_evaluation(): void
    {
        $user = User::factory()->create();
        $evaluation = Evaluation::factory()->create([
            'evaluateur_id' => $user->id,
            'statut' => 'valide',
        ]);

        $this->actingAs($user)
            ->delete(route('admin.evaluations.destroy', $evaluation))
            ->assertRedirect(route('admin.evaluations.index'));

        $this->assertDatabaseHas('evaluations', [
            'id' => $evaluation->id,
            'statut' => 'valide',
        ]);
    }

    public function test_an_authenticated_admin_can_update_a_draft_evaluation(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $agent = Agent::factory()->create(['service_id' => $service->id]);

        Objectif::factory()->create([
            'assignable_type' => Agent::class,
            'assignable_id' => $agent->id,
            'date' => '2026-02-10',
            'avancement_percentage' => 90,
        ]);

        $evaluation = Evaluation::factory()->create([
            'evaluable_type' => Agent::class,
            'evaluable_id' => $agent->id,
            'evaluateur_id' => $user->id,
            'date_debut' => '2026-02-01',
            'date_fin' => '2026-02-28',
            'note_objectifs' => 0,
            'note_finale' => 0,
            'statut' => 'brouillon',
        ]);

        $this->actingAs($user)
            ->put(route('admin.evaluations.update', $evaluation), [
                'evaluable_type' => 'agent',
                'evaluable_id' => $agent->id,
                'date_debut' => '2026-02-01',
                'date_fin' => '2026-02-28',
                'note_manuelle' => 70,
                'commentaire' => 'Mise a jour en brouillon',
            ])
            ->assertRedirect(route('admin.evaluations.show', $evaluation));

        $this->assertDatabaseHas('evaluations', [
            'id' => $evaluation->id,
            'note_objectifs' => 90,
            'note_manuelle' => 70,
            'note_finale' => 80,
            'commentaire' => 'Mise a jour en brouillon',
        ]);
    }

    public function test_an_authenticated_admin_cannot_update_a_non_draft_evaluation(): void
    {
        $user = User::factory()->create();
        $evaluation = Evaluation::factory()->create([
            'evaluateur_id' => $user->id,
            'statut' => 'soumis',
            'commentaire' => 'Initial',
        ]);

        $this->actingAs($user)
            ->put(route('admin.evaluations.update', $evaluation), [
                'evaluable_type' => 'agent',
                'evaluable_id' => $evaluation->evaluable_id,
                'date_debut' => $evaluation->date_debut->toDateString(),
                'date_fin' => $evaluation->date_fin->toDateString(),
                'commentaire' => 'Modification non autorisee',
            ])
            ->assertRedirect(route('admin.evaluations.show', $evaluation));

        $this->assertDatabaseHas('evaluations', [
            'id' => $evaluation->id,
            'commentaire' => 'Initial',
        ]);
    }

    public function test_an_authenticated_admin_can_export_evaluation_pdf(): void
    {
        $user = User::factory()->create();
        $evaluation = Evaluation::factory()->create([
            'evaluateur_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.evaluations.pdf', $evaluation));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
