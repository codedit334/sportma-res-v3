<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function sports()
    {
        return $this->hasMany(Sport::class);
    }
}