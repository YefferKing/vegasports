<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    public function upload(Folder $folder)
    {
        return view('images.upload', compact('folder'));
    }

    public function store(Request $request)
{
    $request->validate([
        'folder_id' => 'required|exists:folders,id',
        'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240'
    ], [
        'images.*.required' => 'Debe seleccionar al menos una imagen.',
        'images.*.image' => 'El archivo debe ser una imagen.',
        'images.*.mimes' => 'Las imágenes deben ser de tipo: jpeg, png, jpg, gif, webp.',
        'images.*.max' => 'Cada imagen no puede ser mayor a 10MB.'
    ]);

    $folder = Folder::findOrFail($request->folder_id);
    $uploadedCount = 0;

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $file) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . Str::random(10) . '.' . $extension;
            
            // ✅ OBTENER DATOS ANTES DE MOVER
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
            
            // Guardar directamente en public/uploads/
            $folderPath = 'uploads/' . Str::slug($folder->nombre);
            $destinationPath = public_path($folderPath);
            
            // Crear carpeta si no existe
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            // Mover archivo directamente a public/
            if ($file->move($destinationPath, $fileName)) {
                // ✅ USAR VARIABLES GUARDADAS
                Image::create([
                    'folder_id' => $folder->id,
                    'image_name' => $originalName,
                    'image_size' => $fileSize,        // ← Variable guardada
                    'image_type' => $mimeType,        // ← Variable guardada
                    'file_path' => $folderPath . '/' . $fileName
                ]);
                
                $uploadedCount++;
            }
        }
    }

    if ($uploadedCount > 0) {
        $message = $uploadedCount === 1 ? 
                   'Imagen subida exitosamente.' : 
                   $uploadedCount . ' imágenes subidas exitosamente.';
        
        return redirect()->route('folders.show', $folder)
                        ->with('success', $message);
    }

    return redirect()->back()
                    ->with('error', 'No se pudo subir ninguna imagen.');
}

public function destroy(Image $image)
{
    // Eliminar archivo físico de public/
    $filePath = public_path($image->file_path);
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    $image->delete();

    return redirect()->back()
                    ->with('success', 'Imagen eliminada exitosamente.');
}

/**
 * Eliminar todas las imágenes de una carpeta
 */
public function destroyAll(Folder $folder)
{
    $images = $folder->images;
    $count = $images->count();
    
    // Eliminar archivos físicos
    foreach ($images as $image) {
        $filePath = public_path($image->file_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    // Eliminar registros de base de datos
    $folder->images()->delete();
    
    return redirect()->route('folders.show', $folder)
                    ->with('success', "Se eliminaron todas las $count imagen(es) exitosamente.");
}

/**
 * Eliminar múltiples imágenes seleccionadas
 */
public function destroyMultiple(Request $request)
{
    $request->validate([
        'image_ids' => 'required|array',
        'image_ids.*' => 'exists:images,id',
        'folder_id' => 'required|exists:folders,id'
    ]);
    
    $images = Image::whereIn('id', $request->image_ids)->get();
    $count = $images->count();
    $folder = Folder::findOrFail($request->folder_id);
    
    // Eliminar archivos físicos
    foreach ($images as $image) {
        $filePath = public_path($image->file_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    // Eliminar registros de base de datos
    Image::whereIn('id', $request->image_ids)->delete();
    
    return redirect()->route('folders.show', $folder)
                    ->with('success', "Se eliminaron $count imagen(es) seleccionadas exitosamente.");
}
}
