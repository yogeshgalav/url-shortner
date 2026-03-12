<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //
    protected $guarded = ['id','created_at', 'deleted_at'];
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
