<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'terrain_id',
        'title',
        'class',
        'split',
        'clickable',
        'duration',
        'editable',
        'price',
        'category',
        'terrain',
        'start',
        'end',
        'content',
        'status',
    ];
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function terrain()
    {
        return $this->belongsTo(Terrain::class);
    }

}