<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'email',
        'phone',
        'bio',
        'logo',
        'sportma_id',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function sports()
    {
        return $this->hasMany(Sport::class);
    }
}