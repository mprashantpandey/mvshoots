<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Owner extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'status'];

    protected $hidden = ['password', 'remember_token'];
}
