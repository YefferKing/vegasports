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
    <!-- CONTROLES DE SELECCIÓN MÚLTIPLE -->
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
                    <button type="button" 
                            class="btn btn-warning me-2" 
                            id="deleteSelectedBtn" 
                            style="display: none;"
                            onclick="deleteSelectedImages()">
                        <i class="fas fa-trash me-1"></i>
                        Eliminar Seleccionadas (<span id="selectedCountBtn">0</span>)
                    </button>
                    
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

    <!-- FORMULARIOS PARA ELIMINACIÓN -->
    <form id="deleteMultipleForm" action="{{ route('images.destroyMultiple') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="folder_id" value="{{ $folder->id }}">
        <div id="selectedImageIds"></div>
    </form>

    <form id="deleteAllForm" action="{{ route('images.destroyAll', $folder) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Grid de imágenes -->
    <div class="row">
        @foreach($images as $index => $image)
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="position-relative">
                        <div class="position-absolute top-0 start-0 m-2">
                            <input class="form-check-input image-checkbox" 
                                   type="checkbox" 
                                   value="{{ $image->id }}" 
                                   id="image{{ $image->id }}"
                                   style="transform: scale(1.2);">
                        </div>
                        
                        <img src="{{ $image->file_path }}" 
                             class="card-img-top" 
                             style="height: 200px; object-fit: cover; cursor: pointer;"
                             alt="{{ $image->image_name }}"
                             onclick="openImageModal({{ $index }})">
                        
                        <span class="position-absolute top-0 end-0 m-2 badge bg-dark">
                            {{ strtoupper(pathinfo($image->image_name, PATHINFO_EXTENSION)) }}
                        </span>
                    </div>
                    
                    <div class="card-body p-3">
                        <h6 class="card-title mb-2" title="{{ $image->image_name }}">
                            {{ Str::limit($image->image_name, 20) }}
                        </h6>
                        
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>{{ number_format($image->image_size / 1024, 1) }} KB</span>
                            <span>{{ $image->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    
                    <div class="card-footer p-2">
                        <div class="btn-group w-100">
                            <button class="btn btn-outline-primary btn-sm" 
                                    onclick="openImageModal({{ $index }})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="{{ $image->file_path }}" 
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
            </div>
        @endforeach
    </div>

    <!-- 🆕 MODAL ÚNICA CON NAVEGACIÓN -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImageTitle">Imagen</h5>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary me-3" id="imageCounter">1 / {{ $images->count() }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body text-center position-relative">
                    <!-- Botones de navegación -->
                    <button type="button" 
                            class="btn btn-dark position-absolute top-50 start-0 translate-middle-y ms-3" 
                            id="prevBtn"
                            onclick="navigateImage(-1)"
                            style="z-index: 10;">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    
                    <button type="button" 
                            class="btn btn-dark position-absolute top-50 end-0 translate-middle-y me-3" 
                            id="nextBtn"
                            onclick="navigateImage(1)"
                            style="z-index: 10;">
                        <i class="fas fa-chevron-right"></i>
                    </button>

                    <!-- Imagen principal -->
                    <img id="modalImage" 
                         class="img-fluid" 
                         style="max-height: 70vh;"
                         alt="">
                    
                    <!-- Información de la imagen -->
                    <div class="mt-3" id="imageInfo">
                        <p class="mb-1"><strong>Tamaño:</strong> <span id="imageSize"></span></p>
                        <p class="mb-1"><strong>Tipo:</strong> <span id="imageType"></span></p>
                        <p class="mb-0"><strong>Subida:</strong> <span id="imageDate"></span></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <a id="downloadBtn" href="#" download class="btn btn-success">
                        <i class="fas fa-download me-1"></i>Descargar
                    </a>
                    
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Eliminar
                        </button>
                    </form>
                    
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
// Variables globales
let currentImageIndex = 0;
let totalImages = {{ $images->count() }};
let allImages = [];

// Cargar datos de imágenes
@foreach($images as $index => $image)
allImages[{{ $index }}] = {
    id: {{ $image->id }},
    name: "{{ addslashes($image->image_name) }}",
    path: "{{ $image->file_path }}",
    size: {{ $image->image_size }},
    type: "{{ $image->image_type }}",
    date: "{{ $image->updated_at->format('d/m/Y H:i:s') }}"
};
@endforeach

// Función para abrir modal
function openImageModal(index) {
    currentImageIndex = index;
    updateModalContent();
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}

// Función para navegar
function navigateImage(direction) {
    currentImageIndex += direction;
    if (currentImageIndex >= totalImages) currentImageIndex = 0;
    if (currentImageIndex < 0) currentImageIndex = totalImages - 1;
    updateModalContent();
}

// Función para actualizar modal
function updateModalContent() {
    const image = allImages[currentImageIndex];
    
    document.getElementById('modalImageTitle').textContent = image.name;
    document.getElementById('imageCounter').textContent = `${currentImageIndex + 1} / ${totalImages}`;
    document.getElementById('modalImage').src = image.path;
    document.getElementById('modalImage').alt = image.name;
    document.getElementById('imageSize').textContent = formatBytes(image.size);
    document.getElementById('imageType').textContent = image.type;
    document.getElementById('imageDate').textContent = image.date;
    document.getElementById('downloadBtn').href = image.path;
    document.getElementById('downloadBtn').download = image.name;
    document.getElementById('deleteForm').action = `/images/${image.id}`;
    
    // Mostrar/ocultar botones de navegación
    document.getElementById('prevBtn').style.display = totalImages > 1 ? 'block' : 'none';
    document.getElementById('nextBtn').style.display = totalImages > 1 ? 'block' : 'none';
}

// Formatear bytes
function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Navegación con teclado y táctil
let touchStartX = 0;
let touchEndX = 0;

document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('imageModal');
    if (modal && modal.classList.contains('show')) {
        switch(e.key) {
            case 'ArrowLeft':
                e.preventDefault();
                navigateImage(-1);
                break;
            case 'ArrowRight':
                e.preventDefault();
                navigateImage(1);
                break;
            case 'Escape':
                bootstrap.Modal.getInstance(modal).hide();
                break;
        }
    }
});

// Soporte táctil para móviles
document.addEventListener('touchstart', function(e) {
    const modal = document.getElementById('imageModal');
    if (modal && modal.classList.contains('show')) {
        touchStartX = e.changedTouches[0].screenX;
    }
});

document.addEventListener('touchend', function(e) {
    const modal = document.getElementById('imageModal');
    if (modal && modal.classList.contains('show')) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }
});

function handleSwipe() {
    const swipeThreshold = 50; // Mínimo desplazamiento para activar
    const diff = touchStartX - touchEndX;
    
    if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
            navigateImage(1); // Swipe izquierda = siguiente
        } else {
            navigateImage(-1); // Swipe derecha = anterior
        }
    }
}

// Resto del código para selección múltiple
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const imageCheckboxes = document.querySelectorAll('.image-checkbox');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    const selectedCount = document.getElementById('selectedCount');
    const selectedCountBtn = document.getElementById('selectedCountBtn');

    function updateSelection() {
        const checkedBoxes = document.querySelectorAll('.image-checkbox:checked');
        const count = checkedBoxes.length;
        
        selectedCount.textContent = count;
        selectedCountBtn.textContent = count;
        
        deleteSelectedBtn.style.display = count > 0 ? 'inline-block' : 'none';
        
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

    selectAllCheckbox.addEventListener('change', function() {
        imageCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelection();
    });

    imageCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelection);
    });
});

function deleteSelectedImages() {
    const checkedBoxes = document.querySelectorAll('.image-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Por favor selecciona al menos una imagen.');
        return;
    }
    
    const count = checkedBoxes.length;
    const message = count === 1 ? '¿Eliminar la imagen seleccionada?' : `¿Eliminar las ${count} imágenes seleccionadas?`;
    
    if (confirm(message)) {
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

function deleteAllImages() {
    if (confirm(`¿Estás seguro de eliminar TODAS las ${totalImages} imágenes? Esta acción no se puede deshacer.`)) {
        document.getElementById('deleteAllForm').submit();
    }
}
</script>
@endpush
@endsection