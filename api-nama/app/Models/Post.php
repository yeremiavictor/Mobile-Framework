<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'foto',
        'judul',
        'keterangan',
    ];

    // Menambahkan accessor pada model untuk foto (Wajib CamelCase)
    protected function image():Attribute
    {
        return Attribute::make(
            get: fn ($foto) => url('/storage/posts'. 'foto'),
        );
    }

}
