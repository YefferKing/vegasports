@extends('layouts.app')

@section('title', 'Editar Carpeta - Vegasports')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">
                    <i class="fas fa-edit me-2"></i>Editar Carpeta "{{ $folder->nombre }}"
                </h4>
            </div>
            
            <div class="card-body">
                <form action="{{ route('folders.update', $folder) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label for="nombre" class="form-label">
                            <i class="fas fa-folder me-1"></i>Nombre de la Carpeta *
                        </label>
                        <input type="text" 
                               class="form-control @error('nombre') is-invalid @enderror" 
                               id="nombre" 
                               name="nombre" 
                               value="{{ old('nombre', $folder->nombre) }}" 
                               required
                               maxlength="255">
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="descripcion" class="form-label">
                            <i class="fas fa-align-left me-1"></i>Descripción (Opcional)
                        </label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                  id="descripcion" 
                                  name="descripcion" 
                                  rows="4" 
                                  maxlength="1000">{{ old('descripcion', $folder->descripcion) }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('folders.show', $folder) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="fas fa-save me-2"></i>Actualizar Carpeta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection