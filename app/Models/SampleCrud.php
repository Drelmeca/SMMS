<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleCrud extends Model
{
    protected $table = 'samplecrud';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'email',
        'phone',
    ];
    public $timestamps = true;
}
