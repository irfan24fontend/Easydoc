<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionTemplate extends Model
{

    protected $fillable = [

        'name',

        'slug',

        'paper_size',

        'html',

        'css',

        'status',

        'created_by'

    ];


    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

}