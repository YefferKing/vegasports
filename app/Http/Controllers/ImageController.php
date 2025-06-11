<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Folder;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;
use Exception;

class ImageController extends Controller
{
    private $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);
    }

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
                try {
                    $originalName = $file->getClientOriginalName();
                    $fileSize = $file->getSize();
                    $mimeType = $file->getMimeType();

                    // Subir a Cloudinary
                    $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                        'folder' => 'fogo/' . \Str::slug($folder->nombre),
                        'public_id' => time() . '_' . uniqid(),
                    ]);

                    // Guardar en la base de datos
                    Image::create([
                        'folder_id' => $folder->id,
                        'image_name' => $originalName,
                        'image_size' => $fileSize,
                        'image_type' => $mimeType,
                        'file_path' => $result['secure_url'],
                        'cloudinary_public_id' => $result['public_id']
                    ]);

                    $uploadedCount++;
                } catch (Exception $e) {
                    continue; // Continuar con la siguiente imagen si hay error
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
        try {
            // Eliminar de Cloudinary
            if ($image->cloudinary_public_id) {
                $this->cloudinary->uploadApi()->destroy($image->cloudinary_public_id);
            }

            $image->delete();

            return redirect()->back()
                            ->with('success', 'Imagen eliminada exitosamente.');
        } catch (Exception $e) {
            return redirect()->back()
                            ->with('error', 'Error al eliminar la imagen.');
        }
    }

    /**
     * Eliminar todas las imágenes de una carpeta
     */
    public function destroyAll(Folder $folder)
    {
        $images = $folder->images;
        $count = $images->count();
        
        // Eliminar de Cloudinary
        foreach ($images as $image) {
            try {
                if ($image->cloudinary_public_id) {
                    $this->cloudinary->uploadApi()->destroy($image->cloudinary_public_id);
                }
            } catch (Exception $e) {
                // Continuar eliminando aunque falle uno
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
        
        // Eliminar de Cloudinary
        foreach ($images as $image) {
            try {
                if ($image->cloudinary_public_id) {
                    $this->cloudinary->uploadApi()->destroy($image->cloudinary_public_id);
                }
            } catch (Exception $e) {
                // Continuar eliminando aunque falle uno
            }
        }
        
        // Eliminar registros de base de datos
        Image::whereIn('id', $request->image_ids)->delete();
        
        return redirect()->route('folders.show', $folder)
                        ->with('success', "Se eliminaron $count imagen(es) seleccionadas exitosamente.");
    }
}