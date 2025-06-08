<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // ✅ Namespace correcto
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Image extends Model
{
    use HasFactory; // ✅ Ahora funcionará

    protected $table = 'images';
    public $timestamps = false;
    
    protected $fillable = [
        'folder_id',
        'image_name',
        'image_size',
        'image_type',
        'file_path'
    ];

    protected $casts = [
        'updated_at' => 'datetime'
    ];

    // Relación con carpeta
    public function folder()
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    // Accessor para formatear tamaño
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->image_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    // Accessor para URL de imagen
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    // Accessor para formatear fecha
    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at ? Carbon::parse($this->updated_at)->format('d/m/Y H:i:s') : 'N/A';
    }
}