<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Validation\Rule;

class AdminUserCrud extends Component
{
    public $users;
    public $name, $email, $role, $user_id;
    public $isEdit = false;
    public $showModal = false;
    public $roles = [
        'PCA', 'DG', 'Assistante_Dg', 'DGA', 'Secretaire_assistante', 'Secretaire_Direction',
        'Secretaire_Technique', 'Secretaire_Caisse', 'Secretaire_Agence', 'Conseillers_Dg',
        'Directeur_Direction', 'Directeur_Caisse', 'Directeur_Tehnique', 'Chefs de service',
        "chef d'agence", 'Agent', 'admin'
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'role' => 'required|string',
    ];

    public function mount()
    {
        $this->users = User::all();
    }

    public function render()
    {
        return view('livewire.admin.admin-user-crud');
    }

    public function create()
    {
        $this->reset(['name', 'email', 'role', 'user_id', 'isEdit']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->user_id = $user->id;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user_id),
            ],
            'role' => ['required', Rule::in($this->roles)],
        ]);

        if ($this->isEdit) {
            $user = User::findOrFail($this->user_id);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
            ]);
        } else {
            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
                'password' => bcrypt('password'), // Default password
            ]);
        }
        $this->users = User::all();
        $this->showModal = false;
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        $this->users = User::all();
    }
}
