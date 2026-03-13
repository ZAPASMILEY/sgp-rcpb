<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_cannot_access_agent_pages(): void
    {
        $this->get(route('admin.agents.index'))->assertRedirect(route('login'));
        $this->get(route('admin.agents.create'))->assertRedirect(route('login'));

        $agent = Agent::factory()->create();

        $this->get(route('admin.agents.show', $agent))->assertRedirect(route('login'));
        $this->get(route('admin.agents.edit', $agent))->assertRedirect(route('login'));
    }

    public function test_an_authenticated_admin_can_open_agent_creation_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.agents.create'));

        $response->assertOk();
        $response->assertSee('Nouvel agent', false);
    }

    public function test_an_authenticated_admin_can_create_an_agent_without_photo(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['nom' => 'Service Evaluation']);

        $response = $this->actingAs($user)->post(route('admin.agents.store'), [
            'service_id' => $service->id,
            'nom' => 'Ouedraogo',
            'prenom' => 'Mariam',
            'fonction' => 'Analyste performance',
            'numero_telephone' => '+22670000000',
            'email' => 'mariam.ouedraogo@rcpb.bf',
        ]);

        $response->assertRedirect(route('admin.agents.index'));

        $this->assertDatabaseHas('agents', [
            'service_id' => $service->id,
            'nom' => 'Ouedraogo',
            'prenom' => 'Mariam',
            'fonction' => 'Analyste performance',
            'numero_telephone' => '+22670000000',
            'email' => 'mariam.ouedraogo@rcpb.bf',
            'photo_path' => null,
        ]);
    }

    public function test_an_authenticated_admin_can_create_an_agent_with_imported_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $service = Service::factory()->create(['nom' => 'Service Audit']);
        $photo = $this->fakePngPhoto('agent.png');

        $response = $this->actingAs($user)->post(route('admin.agents.store'), [
            'service_id' => $service->id,
            'nom' => 'Diallo',
            'prenom' => 'Aminata',
            'fonction' => 'Charge de mission',
            'numero_telephone' => '+22671000000',
            'email' => 'aminata.diallo@rcpb.bf',
            'photo_import' => $photo,
        ]);

        $response->assertRedirect(route('admin.agents.index'));

        $agent = Agent::query()->where('email', 'aminata.diallo@rcpb.bf')->firstOrFail();

        $this->assertNotNull($agent->photo_path);
        $this->assertTrue(Storage::disk('public')->exists((string) $agent->photo_path));
    }

    public function test_an_authenticated_admin_can_view_an_agent(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['nom' => 'Service RH']);
        $agent = Agent::factory()->create([
            'service_id' => $service->id,
            'nom' => 'Sawadogo',
            'prenom' => 'Eric',
        ]);

        $response = $this->actingAs($user)->get(route('admin.agents.show', $agent));

        $response->assertOk();
        $response->assertSee('Eric Sawadogo', false);
    }

    public function test_an_authenticated_admin_can_update_an_agent_and_remove_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $service = Service::factory()->create(['nom' => 'Service Exploitation']);
        $storedPhoto = $this->fakePngPhoto('initial.png')->store('agents', 'public');
        $agent = Agent::factory()->create([
            'service_id' => $service->id,
            'photo_path' => $storedPhoto,
        ]);

        $response = $this->actingAs($user)->put(route('admin.agents.update', $agent), [
            'service_id' => $service->id,
            'nom' => 'Ilboudo',
            'prenom' => 'Rita',
            'fonction' => 'Superviseur',
            'numero_telephone' => '+22676000000',
            'email' => 'rita.ilboudo@rcpb.bf',
            'remove_photo' => '1',
        ]);

        $response->assertRedirect(route('admin.agents.show', $agent));

        $this->assertDatabaseHas('agents', [
            'id' => $agent->id,
            'service_id' => $service->id,
            'nom' => 'Ilboudo',
            'prenom' => 'Rita',
            'fonction' => 'Superviseur',
            'numero_telephone' => '+22676000000',
            'email' => 'rita.ilboudo@rcpb.bf',
            'photo_path' => null,
        ]);

        $this->assertFalse(Storage::disk('public')->exists($storedPhoto));
    }

    public function test_an_authenticated_admin_can_delete_an_agent_and_its_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $service = Service::factory()->create(['nom' => 'Service Credit']);
        $storedPhoto = $this->fakePngPhoto('agent.png')->store('agents', 'public');
        $agent = Agent::factory()->create([
            'service_id' => $service->id,
            'photo_path' => $storedPhoto,
        ]);

        $response = $this->actingAs($user)->delete(route('admin.agents.destroy', $agent));

        $response->assertRedirect(route('admin.agents.index'));
        $this->assertDatabaseMissing('agents', ['id' => $agent->id]);
        $this->assertFalse(Storage::disk('public')->exists($storedPhoto));
    }

    public function test_an_authenticated_admin_can_search_agents(): void
    {
        $user = User::factory()->create();

        Agent::factory()->create([
            'nom' => 'Bado',
            'prenom' => 'Clarisse',
            'fonction' => 'Controle interne',
        ]);

        Agent::factory()->create([
            'nom' => 'Compaore',
            'prenom' => 'Issa',
            'fonction' => 'Ressources humaines',
        ]);

        $response = $this->actingAs($user)->get(route('admin.agents.index', [
            'search' => 'Clarisse',
        ]));

        $response->assertOk();
        $response->assertSee('Bado', false);
        $response->assertDontSee('Compaore', false);
    }

    public function test_agent_index_is_paginated_for_authenticated_admin(): void
    {
        $user = User::factory()->create();

        Agent::factory()->count(11)->create();

        $responsePageOne = $this->actingAs($user)->get(route('admin.agents.index'));

        $responsePageOne->assertOk();
        $responsePageOne->assertViewHas('agents', function ($paginator): bool {
            return $paginator->perPage() === 10
                && $paginator->total() === 11
                && $paginator->currentPage() === 1
                && $paginator->count() === 10;
        });

        $responsePageTwo = $this->actingAs($user)->get(route('admin.agents.index', ['page' => 2]));

        $responsePageTwo->assertOk();
        $responsePageTwo->assertViewHas('agents', function ($paginator): bool {
            return $paginator->currentPage() === 2 && $paginator->count() === 1;
        });
    }

    private function fakePngPhoto(string $name = 'photo.png'): UploadedFile
    {
        // 1x1 transparent PNG to avoid requiring GD in tests.
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO5W3n0AAAAASUVORK5CYII=', true);

        if (! is_string($png)) {
            throw new \RuntimeException('Unable to create fake PNG payload.');
        }

        return UploadedFile::fake()->createWithContent($name, $png);
    }
}