<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SongController extends Controller
{
    /**
     * Display a listing of songs.
     */
    public function index(Request $request): View
    {
        $query = Song::with('category');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('songtitle', 'like', "%{$search}%")
                    ->orWhere('songsinger', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('songcategory', $request->input('category'));
        }

        $songs = $query->orderBy('songtitle')->paginate(10)->withQueryString();
        $categories = Category::orderBy('songcategoryname')->get();

        return view('admin.songs.index', compact('songs', 'categories'));
    }

    /**
     * Store a newly created song in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'songtitle' => ['required', 'string', 'max:255'],
            'songsinger' => ['required', 'string', 'max:255'],
            'songcategory' => ['required', 'integer', 'exists:categories,songcategoryid'],
            'songnada' => ['nullable', 'string', 'in:pria,wanita,-'],
            'songduration' => ['nullable', 'string', 'max:5'],
            'songurl' => ['required', 'string'],
        ], [
            'songtitle.required' => 'Judul lagu wajib diisi.',
            'songsinger.required' => 'Pencipta wajib diisi.',
            'songcategory.required' => 'Kategori lagu wajib dipilih.',
            'songcategory.exists' => 'Kategori yang dipilih tidak valid.',
            'songurl.required' => 'URL / path file lagu wajib diisi.',
            'songnada.in' => 'Pilihan nada harus berupa pria, wanita, atau -.',
        ]);

        Song::create($validated);

        return redirect()->route('admin.songs.index')->with('success', 'Lagu berhasil ditambahkan ke katalog.');
    }

    /**
     * Update the specified song in storage.
     */
    public function update(Request $request, Song $song): RedirectResponse
    {
        $validated = $request->validate([
            'songtitle' => ['required', 'string', 'max:255'],
            'songsinger' => ['required', 'string', 'max:255'],
            'songcategory' => ['required', 'integer', 'exists:categories,songcategoryid'],
            'songnada' => ['nullable', 'string', 'in:pria,wanita,-'],
            'songduration' => ['nullable', 'string', 'max:5'],
            'songurl' => ['required', 'string'],
        ], [
            'songtitle.required' => 'Judul lagu wajib diisi.',
            'songsinger.required' => 'Pencipta wajib diisi.',
            'songcategory.required' => 'Kategori lagu wajib dipilih.',
            'songcategory.exists' => 'Kategori yang dipilih tidak valid.',
            'songurl.required' => 'URL / path file lagu wajib diisi.',
            'songnada.in' => 'Pilihan nada harus berupa pria, wanita, atau -.',
        ]);

        $song->update($validated);

        return redirect()->route('admin.songs.index')->with('success', 'Data lagu berhasil diperbarui.');
    }

    /**
     * Remove the specified song from storage.
     */
    public function destroy(Song $song): RedirectResponse
    {
        $song->delete();

        return redirect()->route('admin.songs.index')->with('success', 'Lagu berhasil dihapus dari katalog.');
    }
}
