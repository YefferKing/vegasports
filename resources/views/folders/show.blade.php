@extends('layouts.app')

@section('title', $folder->nombre . ' - Vegasports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="fas fa-folder-open text-warning me-2"></i>{{ $folder->nombre }}
        </h1>
        <p class="text-muted mb-0">{{ $folder->descripcion ?: 'Sin descripción' }}</p>
    </div>
    <div>
        <a href="{{ route('images.upload', $folder) }}" class="btn btn-success me-2">
            <i class="fas fa-upload me-2"></i>Subir Imágenes
        </a>
        <a href="{{ route('folders.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

@if($images->isEmpty())
    <div class="text-center py-5">
        <i class="fas fa-images fa-5x text-muted mb-3"></i>
        <h4 class="text-muted">No hay imágenes en esta carpeta</h4>
        <p class="text-muted mb-4">Sube tus primeras imágenes para comenzar.</p>
        <a href="{{ route('images.upload', $folder) }}" class="btn btn-success btn-lg">
            <i class="fas fa-upload me-2"></i>Subir Primera Imagen
        </a>
    </div>
@else
    <!-- 🆕 CONTROLES DE SELECCIÓN MÚLTIPLE -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                        <label class="form-check-label" for="selectAll">
                            <strong>Seleccionar todas las imágenes</strong>
                        </label>
                    </div>
                    <small class="text-muted">
                        <span id="selectedCount">0</span> imagen(es) seleccionada(s)
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <!-- Botón eliminar seleccionadas -->
                    <button type="button" 
                            class="btn btn-warning me-2" 
                            id="deleteSelectedBtn" 
                            style="display: none;"
                            onclick="deleteSelectedImages()">
                        <i class="fas fa-trash me-1"></i>
                        Eliminar Seleccionadas (<span id="selectedCountBtn">0</span>)
                    </button>
                    
                    <!-- Botón eliminar todas -->
                    <button type="button" 
                            class="btn btn-danger" 
                            onclick="deleteAllImages()">
                        <i class="fas fa-trash me-1"></i>
                        Eliminar Todas ({{ $images->count() }})
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 🆕 FORMULARIO PARA ELIMINACIÓN MÚLTIPLE -->
    <form id="deleteMultipleForm" action="{{ route('images.destroyMultiple') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="folder_id" value="{{ $folder->id }}">
        <div id="selectedImageIds"></div>
    </form>

    <!-- 🆕 FORMULARIO PARA ELIMINAR TODAS -->
    <form id="deleteAllForm" action="{{ route('images.destroyAll', $folder) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Grid de imágenes -->
    <div class="row">
        @foreach($images as $image)
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="position-relative">
                        <!-- 🆕 CHECKBOX DE SELECCIÓN -->
                        <div class="position-absolute top-0 start-0 m-2">
                            <input class="form-check-input image-checkbox" 
                                   type="checkbox" 
                                   value="{{ $image->id }}" 
                                   id="image{{ $image->id }}"
                                   style="transform: scale(1.2);">
                        </div>
                        
                        <img src="{{ asset($image->file_path) }}" 
                             class="card-img-top" 
                             style="height: 200px; object-fit: cover; cursor: pointer;"
                             alt="{{ $image->image_name }}"
                             data-bs-toggle="modal" 
                             data-bs-target="#imageModal{{ $image->id }}">
                        
                        <!-- Badge de tipo -->
                        <span class="position-absolute top-0 end-0 m-2 badge bg-dark">
                            {{ strtoupper(pathinfo($image->image_name, PATHINFO_EXTENSION)) }}
                        </span>
                    </div>
                    
                    <div class="card-body p-3">
                        <h6 class="card-title mb-2" title="{{ $image->image_name }}">
                            {{ Str::limit($image->image_name, 20) }}
                        </h6>
                        
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>{{ $image->formatted_size }}</span>
                            <span>{{ $image->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    
                    <div class="card-footer p-2">
                        <div class="btn-group w-100">
                            <button class="btn btn-outline-primary btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#imageModal{{ $image->id }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="{{ asset($image->file_path) }}" 
                               download="{{ $image->image_name }}"
                               class="btn btn-outline-success btn-sm">
                                <i class="fas fa-download"></i>
                            </a>
                            <form action="{{ route('images.destroy', $image) }}" 
                                  method="POST" 
                                  class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-outline-danger btn-sm"
                                        onclick="return confirm('¿Eliminar imagen {{ $image->image_name }}?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Modal para ver imagen completa -->
                <div class="modal fade" id="imageModal{{ $image->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ $image->image_name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="{{ asset($image->file_path) }}" 
                                     class="img-fluid" 
                                     alt="{{ $image->image_name }}">
                                
                                <div class="mt-3">
                                    <p class="mb-1"><strong>Tamaño:</strong> {{ $image->formatted_size }}</p>
                                    <p class="mb-1"><strong>Tipo:</strong> {{ $image->image_type }}</p>
                                    <p class="mb-0"><strong>Subida:</strong> {{ $image->updated_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <a href="{{ asset($image->file_path) }}" 
                                   download="{{ $image->image_name }}"
                                   class="btn btn-success">
                                    <i class="fas fa-download me-1"></i>Descargar
                                </a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@push('scripts')
<script>
// 🆕 JAVASCRIPT PARA SELECCIÓN MÚLTIPLE
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const imageCheckboxes = document.querySelectorAll('.image-checkbox');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    const selectedCount = document.getElementById('selectedCount');
    const selectedCountBtn = document.getElementById('selectedCountBtn');

    // Función para actualizar contadores y botón
    function updateSelection() {
        const checkedBoxes = document.querySelectorAll('.image-checkbox:checked');
        const count = checkedBoxes.length;
        
        selectedCount.textContent = count;
        selectedCountBtn.textContent = count;
        
        if (count > 0) {
            deleteSelectedBtn.style.display = 'inline-block';
        } else {
            deleteSelectedBtn.style.display = 'none';
        }
        
        // Actualizar estado del checkbox "Seleccionar todas"
        if (count === imageCheckboxes.length && count > 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else if (count > 0) {
            selectAllCheckbox.indeterminate = true;
            selectAllCheckbox.checked = false;
        } else {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        }
    }

    // Event listener para "Seleccionar todas"
    selectAllCheckbox.addEventListener('change', function() {
        imageCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelection();
    });

    // Event listeners para checkboxes individuales
    imageCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelection);
    });
});

// Función para eliminar imágenes seleccionadas
function deleteSelectedImages() {
    const checkedBoxes = document.querySelectorAll('.image-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
        alert('Por favor selecciona al menos una imagen.');
        return;
    }
    
    const count = checkedBoxes.length;
    const message = count === 1 ? 
        '¿Eliminar la imagen seleccionada?' : 
        `¿Eliminar las ${count} imágenes seleccionadas?`;
    
    if (confirm(message)) {
        // Agregar inputs hidden con los IDs
        const form = document.getElementById('deleteMultipleForm');
        const container = document.getElementById('selectedImageIds');
        container.innerHTML = '';
        
        checkedBoxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'image_ids[]';
            input.value = checkbox.value;
            container.appendChild(input);
        });
        
        form.submit();
    }
}

// Función para eliminar todas las imágenes
function deleteAllImages() {
    const totalImages = {{ $images->count() }};
    
    if (confirm(`¿Estás seguro de eliminar TODAS las ${totalImages} imágenes de esta carpeta? Esta acción no se puede deshacer.`)) {
        document.getElementById('deleteAllForm').submit();
    }
}
</script>
@endpush
@endsection