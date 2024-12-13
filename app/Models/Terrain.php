<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Terrain extends Model
{
    use HasFactory;
    protected $fillable = ['sport_id', 'label', 'prices', 'terrainID','address', 'sportma', 'sportma_terrain_id'];

    protected $casts = [
        'prices' => 'array',
    ];

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }
}