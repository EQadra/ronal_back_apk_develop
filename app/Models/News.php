<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToCompany;

class News extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'titulo',
        'descripcion',
        'url',
        'fecha_publicacion',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
