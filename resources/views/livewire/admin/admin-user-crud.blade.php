<div>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Gestion des utilisateurs</h2>
        <button wire:click="create" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">Ajouter un utilisateur</button>
    </div>

    <table class="min-w-full bg-white rounded shadow">
        <thead>
            <tr>
                <th class="px-4 py-2">Nom</th>
                <th class="px-4 py-2">Email</th>
                <th class="px-4 py-2">Rôle</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $user->name }}</td>
                    <td class="px-4 py-2">{{ $user->email }}</td>
                    <td class="px-4 py-2">{{ $user->role }}</td>
                    <td class="px-4 py-2">
                        <button wire:click="edit({{ $user->id }})" class="text-blue-600 hover:underline mr-2">Modifier</button>
                        <button wire:click="delete({{ $user->id }})" class="text-red-600 hover:underline">Supprimer</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Modal -->
    <div x-data="{ show: @entangle('showModal') }" x-show="show" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50" style="display: none;">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md relative">
            <button @click="show = false" wire:click="$set('showModal', false)" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
            <h3 class="text-lg font-semibold mb-4">{{ $isEdit ? 'Modifier' : 'Ajouter' }} un utilisateur</h3>
            <form wire:submit.prevent="save">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Nom</label>
                    <input type="text" wire:model.defer="name" class="w-full border rounded px-3 py-2" required>
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" wire:model.defer="email" class="w-full border rounded px-3 py-2" required>
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Rôle</label>
                    <select wire:model.defer="role" class="w-full border rounded px-3 py-2" required>
                        <option value="">Sélectionner un rôle</option>
                        @foreach($roles as $roleOption)
                            <option value="{{ $roleOption }}">{{ $roleOption }}</option>
                        @endforeach
                    </select>
                    @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">{{ $isEdit ? 'Mettre à jour' : 'Créer' }}</button>
                </div>
            </form>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Rôle</label>
                        <input type="text" class="w-full border rounded px-3 py-2 bg-gray-100" value="{{ $role ?? ($user->role ?? '') }}" readonly>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">{{ $isEdit ? 'Mettre à jour' : 'Créer' }}</button>
                    </div>
                </form>
        </div>
    </div>
</div>
