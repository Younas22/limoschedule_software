<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Translation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TranslationController extends Controller
{
    public function edit(Language $language): View
    {
        $keys = Translation::knownKeys();

        $values = Translation::where('language_id', $language->id)
            ->where('group', '*')
            ->pluck('value', 'key');

        return view('admin.languages.translations', compact('language', 'keys', 'values'));
    }

    public function update(Request $request, Language $language): RedirectResponse
    {
        $data = $request->validate([
            'translations' => ['array'],
            'translations.*' => ['nullable', 'string'],
            'new_translations' => ['array'],
            'new_translations.*.key' => ['nullable', 'string', 'max:255'],
            'new_translations.*.value' => ['nullable', 'string'],
        ]);

        foreach ($data['translations'] ?? [] as $key => $value) {
            Translation::updateOrCreate(
                ['language_id' => $language->id, 'group' => '*', 'key' => $key],
                ['value' => $value ?? '']
            );
        }

        foreach ($data['new_translations'] ?? [] as $row) {
            if (filled($row['key'] ?? null)) {
                Translation::updateOrCreate(
                    ['language_id' => $language->id, 'group' => '*', 'key' => $row['key']],
                    ['value' => $row['value'] ?? '']
                );
            }
        }

        return back()->with('status', 'Translations updated successfully.');
    }

    public function destroy(Request $request, Language $language): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string'],
        ]);

        Translation::where('language_id', $language->id)
            ->where('group', '*')
            ->where('key', $data['key'])
            ->delete();

        return back()->with('status', 'String deleted successfully.');
    }
}
