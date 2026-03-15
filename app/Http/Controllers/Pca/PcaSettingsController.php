<?php

namespace App\Http\Controllers\Pca;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PcaSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('pca.settings.edit', [
            'theme' => $request->user()->theme_preference ?? 'reference',
        ]);
    }

    public function updateTheme(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme_preference' => ['required', 'string', 'in:reference,classic'],
        ]);

        $request->user()->forceFill([
            'theme_preference' => $validated['theme_preference'],
        ])->save();

        return redirect()
            ->route('pca.settings.edit')
            ->with('status', 'Theme mis a jour avec succes.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($validated['current_password'], (string) $request->user()->password)) {
            return back()->withErrors([
                'current_password' => 'Le mot de passe actuel est incorrect.',
            ])->withInput();
        }

        $request->user()->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return redirect()
            ->route('pca.settings.edit')
            ->with('status', 'Mot de passe mis a jour avec succes.');
    }
}
