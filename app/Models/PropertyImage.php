<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyImage extends Model
{
    use HasFactory;
    protected $table = 'property_images';
    protected $fillable = ['property_id', 'path'];

    protected $connection = 'mysql_sec'; // Specify the secondary database connection

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
