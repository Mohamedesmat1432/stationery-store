<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Update the application locale.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'locale' => ['required', 'string', 'in:en,ar'],
        ]);

        $locale = $request->input('locale');

        session()->put('locale', $locale);

        if ($user = $request->user()) {
            $user->update(['locale' => $locale]);
        }

        return back()->with('flash', [
            'type' => 'success',
            'message' => __('Language updated successfully.'),
        ]);
    }
}
