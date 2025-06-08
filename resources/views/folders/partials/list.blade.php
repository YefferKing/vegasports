<?php if ($folders->isEmpty()): ?>
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="fas fa-folder-open fa-5x text-muted"></i>
        </div>
        <h4 class="text-muted">No tienes carpetas aún</h4>
        <p class="text-muted mb-4">Crea tu primera carpeta para comenzar a organizar tus imágenes por categorías.</p>
        <a href="<?= route('folders.create') ?>" class="btn btn-primary btn-lg">
            <i class="fas fa-plus me-2"></i>Crear Primera Carpeta
        </a>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($folders as $folder): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header folder-card">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-folder me-2"></i><?= htmlspecialchars($folder->nombre) ?>
                        </h5>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <?php if ($folder->descripcion): ?>
                            <p class="card-text text-muted flex-grow-1"><?= htmlspecialchars(Str::limit($folder->descripcion, 100)) ?></p>
                        <?php else: ?>
                            <p class="card-text text-muted flex-grow-1 fst-italic">Sin descripción</p>
                        <?php endif; ?>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary fs-6">
                                    <i class="fas fa-images me-1"></i><?= $folder->images_count ?> imagen(es)
                                </span>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i><?= $folder->creado_formatted ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100" role="group">
                            <a href="<?= route('folders.show', $folder) ?>" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>Ver
                            </a>
                            <a href="<?= route('images.upload', $folder) ?>" 
                               class="btn btn-outline-success">
                                <i class="fas fa-upload me-1"></i>Subir
                            </a>
                            <a href="<?= route('folders.edit', $folder) ?>" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-edit me-1"></i>Editar
                            </a>
                            <form action="<?= route('folders.destroy', $folder) ?>" 
                                  method="POST" 
                                  class="d-inline">
                                <?= csrf_field() ?>
                                <?= method_field('DELETE') ?>
                                <button type="submit" 
                                        class="btn btn-outline-danger" 
                                        onclick="return confirmDelete('¿Eliminar la carpeta &quot;<?= htmlspecialchars($folder->nombre) ?>&quot; y todas sus <?= $folder->images_count ?> imagen(es)?')">
                                    <i class="fas fa-trash me-1"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>