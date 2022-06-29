<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class App_User extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $hidden = [
        'password', 'remember_token',  
    ];
}
