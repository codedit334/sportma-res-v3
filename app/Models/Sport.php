<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'type',
        'slug',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function terrains()
    {
        return $this->hasMany(Terrain::class);
    }
}