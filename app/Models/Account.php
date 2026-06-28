<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    // Added the new fields to the fillable array
    protected $fillable = ['name', 'group_type', 'balance', 'mobile_number', 'address', 'is_active'];

    // An account has many entries in the ledger
    public function entries()
    {
        return $this->hasMany(VoucherEntry::class);
    }
}