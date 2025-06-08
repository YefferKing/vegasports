@extends('layouts.app')

@section('title', 'Nueva Carpeta - Vegasports')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-plus me-2"></i>Crear Nueva Carpeta
                </h4>
            </div>
            
            <div class="card-body">
                <form action="{{ route('folders.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="nombre" class="form-label">
                            <i class="fas fa-folder me-1"></i>Nombre de la Carpeta *
                        </label>
                        <input type="text" 
                               class="form-control @error('nombre') is-invalid @enderror" 
                               id="nombre" 
                               name="nombre" 
                               value="{{ old('nombre') }}" 
                               placeholder="Ej: Vacaciones 2024, Familia, Trabajo..."
                               required
                               maxlength="255">
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Solo letras, números, espacios, guiones y guiones bajos.
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="descripcion" class="form-label">
                            <i class="fas fa-align-left me-1"></i>Descripción (Opcional)
                        </label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                  id="descripcion" 
                                  name="descripcion" 
                                  rows="4" 
                                  placeholder="Describe qué tipo de imágenes contendrá esta carpeta..."
                                  maxlength="1000">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <span id="charCount">0</span>/1000 caracteres
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('folders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Crear Carpeta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Contador de caracteres
    const descripcion = document.getElementById('descripcion');
    const charCount = document.getElementById('charCount');
    
    descripcion.addEventListener('input', function() {
        charCount.textContent = this.value.length;
        
        if (this.value.length > 800) {
            charCount.style.color = '#dc3545';
        } else if (this.value.length > 600) {
            charCount.style.color = '#ffc107';
        } else {
            charCount.style.color = '#6c757d';
        }
    });
    
    // Trigger inicial
    descripcion.dispatchEvent(new Event('input'));
</script>
@endpush
@endsection