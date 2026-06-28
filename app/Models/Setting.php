<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    // 🚨 Added favicon_path
    protected $fillable = ['company_name', 'address', 'phone', 'email', 'logo_path', 'favicon_path'];
}