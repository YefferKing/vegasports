@extends('layouts.app')

@section('title', 'Mis Carpetas - Vegasports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="fas fa-folder text-primary me-2"></i>Mis Carpetas
        </h1>
        <p class="text-muted mb-0">Organiza tus imágenes por categorías</p>
    </div>
    <a href="{{ route('folders.create') }}" class="btn btn-primary btn-lg">
        <i class="fas fa-plus me-2"></i>Nueva Carpeta
    </a>
</div>

<form id="search-form" method="GET" action="{{ route('folders.index') }}" class="mb-4">
    <div class="input-group">
        <input type="text" name="search" id="search-input" class="form-control" placeholder="Buscar carpeta..." value="{{ request('search') }}">
        <button class="btn btn-outline-primary" type="submit">
            <i class="fas fa-search"></i>
        </button>
    </div>
</form>
<div id="folders-list">
    @include('folders.partials.list', ['folders' => $folders])
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchForm = document.getElementById('search-form');
    let timeout = null;

    // Evita el envío del formulario al presionar Enter
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
    });

    searchInput.addEventListener('keyup', function() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            let query = searchInput.value;
            fetch(`{{ route('folders.index') }}?search=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, 'text/html');
                let list = doc.getElementById('folders-list');
                document.getElementById('folders-list').innerHTML = list ? list.innerHTML : html;
            });
        }, 300);
    });
});
</script>
@endpush