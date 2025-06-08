<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // ✅ Namespace correcto
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Folder extends Model
{
    use HasFactory; // ✅ Ahora funcionará

    protected $table = 'folders';
    public $timestamps = false;
    
    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    protected $casts = [
        'creado' => 'datetime',
        'actualizado' => 'datetime'
    ];

    // Relación con imágenes
    public function images()
    {
        return $this->hasMany(Image::class, 'folder_id')->orderBy('updated_at', 'desc');
    }

    // Obtener carpetas con conteo de imágenes
    public static function withImageCount()
    {
        return self::withCount('images')->orderBy('creado', 'desc')->get();
    }

    // Accessor para formatear fecha
    public function getCreadoFormattedAttribute()
    {
        return $this->creado ? Carbon::parse($this->creado)->format('d/m/Y H:i') : 'N/A';
    }

    public function getActualizadoFormattedAttribute()
    {
        return $this->actualizado ? Carbon::parse($this->actualizado)->format('d/m/Y H:i') : 'N/A';
    }
}