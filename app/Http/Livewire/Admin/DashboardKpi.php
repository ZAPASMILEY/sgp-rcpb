<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use App\Models\Structure;
use App\Models\User;

class DashboardKpi extends Component
{
    public $totalDirections;
    public $totalCaisses;
    public $totalAgences;
    public $totalGuichets;
    public $totalAgents;
    public $newAgentsThisWeek;

    public function mount()
    {
        $this->totalDirections = Structure::where('type', 'direction')->count();
        $this->totalCaisses = Structure::where('type', 'caisse')->count();
        $this->totalAgences = Structure::where('type', 'agence')->count();
        $this->totalGuichets = Structure::where('type', 'guichet')->count();
        $this->totalAgents = User::count();
        $this->newAgentsThisWeek = User::where('created_at', '>=', now()->startOfWeek())->count();
    }

    public function render()
    {
        return view('livewire.admin.dashboard-kpi');
    }
}
