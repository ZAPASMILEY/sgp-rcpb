<?php

namespace App\Http\Livewire\Admin;

use App\Models\Agent;
use App\Models\Direction;
use App\Models\Service;
use App\Models\User;
use Livewire\Component;

class DashboardKpi extends Component
{
    public $totalDirections;
    public $totalServices;
    public $totalAgents;
    public $totalSecretaires;

    public function mount()
    {
        $this->totalDirections = Direction::query()->count();
        $this->totalServices = Service::query()->count();
        $this->totalAgents = Agent::query()->count();
        $this->totalSecretaires = User::query()->where('role', 'secretaire')->count();
    }

    public function render()
    {
        return view('livewire.admin.dashboard-kpi');
    }
}
