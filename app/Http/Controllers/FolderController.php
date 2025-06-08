<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FolderController extends Controller
{
    public function index(Request $request)
    {
        $query = Folder::query();

        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        $folders = $query->withCount('images')->get();

        return view('folders.index', compact('folders'));
    }

    public function create()
    {
        return view('folders.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|unique:folders|max:255|regex:/^[a-zA-Z0-9\s\-_]+$/',
            'descripcion' => 'nullable|max:1000'
        ], [
            'nombre.required' => 'El nombre de la carpeta es obligatorio.',
            'nombre.unique' => 'Ya existe una carpeta con este nombre.',
            'nombre.regex' => 'El nombre solo puede contener letras, números, espacios, guiones y guiones bajos.',
            'descripcion.max' => 'La descripción no puede tener más de 1000 caracteres.'
        ]);

        $folder = Folder::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ]);

        // Crear carpeta física
        $folderPath = 'uploads/' . Str::slug($folder->nombre);
        if (!Storage::disk('public')->exists($folderPath)) {
            Storage::disk('public')->makeDirectory($folderPath);
        }

        return redirect()->route('folders.index')
                        ->with('success', 'Carpeta "' . $folder->nombre . '" creada exitosamente.');
    }

    public function show(Folder $folder)
    {
        $images = $folder->images()->orderBy('updated_at', 'desc')->get();
        
        return view('folders.show', compact('folder', 'images'));
    }

    public function edit(Folder $folder)
    {
        return view('folders.edit', compact('folder'));
    }

    public function update(Request $request, Folder $folder)
    {
        $request->validate([
            'nombre' => 'required|unique:folders,nombre,' . $folder->id . '|max:255|regex:/^[a-zA-Z0-9\s\-_]+$/',
            'descripcion' => 'nullable|max:1000'
        ], [
            'nombre.required' => 'El nombre de la carpeta es obligatorio.',
            'nombre.unique' => 'Ya existe una carpeta con este nombre.',
            'nombre.regex' => 'El nombre solo puede contener letras, números, espacios, guiones y guiones bajos.',
        ]);

        $folder->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ]);

        return redirect()->route('folders.index')
                        ->with('success', 'Carpeta actualizada exitosamente.');
    }

    public function destroy(Folder $folder)
    {
        // Eliminar carpeta física y todas las imágenes
        $folderPath = 'uploads/' . Str::slug($folder->nombre);
        if (Storage::disk('public')->exists($folderPath)) {
            Storage::disk('public')->deleteDirectory($folderPath);
        }

        $imageCount = $folder->images()->count();
        $folder->delete();

        return redirect()->route('folders.index')
                        ->with('success', 'Carpeta eliminada exitosamente. Se eliminaron ' . $imageCount . ' imagen(es).');
    }
}
