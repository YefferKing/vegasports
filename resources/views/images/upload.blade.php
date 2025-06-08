@extends('layouts.app')

@section('title', 'Subir Imágenes - Vegasports')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">
                    <i class="fas fa-upload me-2"></i>Subir Imágenes a "{{ $folder->nombre }}"
                </h4>
            </div>
            
            <div class="card-body">
                <form action="{{ route('images.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="folder_id" value="{{ $folder->id }}">
                    
                    <div class="mb-4">
                        <label for="images" class="form-label">
                            <i class="fas fa-images me-1"></i>Seleccionar Imágenes *
                        </label>
                        <input type="file" 
                               class="form-control @error('images.*') is-invalid @enderror" 
                               id="images" 
                               name="images[]" 
                               multiple 
                               accept="image/*"
                               onchange="previewImages(this)"
                               required>
                        @error('images.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Selecciona múltiples imágenes (JPEG, PNG, JPG, GIF, WEBP). Máximo 10MB por imagen.
                        </div>
                    </div>
                    
                    <!-- Preview de imágenes -->
                    <div class="mb-4">
                        <label class="form-label">Vista previa:</label>
                        <div id="imagePreview" class="row"></div>
                    </div>
                    
                    <!-- Información de la carpeta -->
                    <div class="mb-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-folder me-1"></i>Información de la Carpeta
                                </h6>
                                <p class="card-text mb-1">
                                    <strong>Nombre:</strong> {{ $folder->nombre }}
                                </p>
                                @if($folder->descripcion)
                                    <p class="card-text mb-1">
                                        <strong>Descripción:</strong> {{ $folder->descripcion }}
                                    </p>
                                @endif
                                <p class="card-text mb-0">
                                    <strong>Imágenes actuales:</strong> 
                                    <span class="badge bg-primary">{{ $folder->images()->count() }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('folders.show', $folder) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver a Carpeta
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload me-2"></i>Subir Imágenes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewImages(input) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    if (input.files) {
        const files = Array.from(input.files);
        const maxPreview = 6; // Máximo 6 previews
        
        files.slice(0, maxPreview).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'col-md-4 col-sm-6 mb-3';
                    div.innerHTML = `
                        <div class="card">
                            <img src="${e.target.result}" 
                                 class="card-img-top" 
                                 style="height: 150px; object-fit: cover;">
                            <div class="card-body p-2">
                                <small class="text-muted">${file.name}</small><br>
                                <small class="badge bg-info">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                            </div>
                        </div>
                    `;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
        
        if (files.length > maxPreview) {
            const div = document.createElement('div');
            div.className = 'col-md-4 col-sm-6 mb-3';
            div.innerHTML = `
                <div class="card bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                    <div class="text-center">
                        <i class="fas fa-plus-circle fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">+${files.length - maxPreview} más</p>
                    </div>
                </div>
            `;
            preview.appendChild(div);
        }
    }
}
</script>
@endpush
@endsection