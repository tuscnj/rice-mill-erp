<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    // 🚨 This line is critical! If mobile_number and address are missing here, they will never save!
    protected $fillable = ['name', 'group_type', 'balance', 'mobile_number', 'address', 'is_active'];

    public function entries()
    {
        return $this->hasMany(VoucherEntry::class);
    }
}