<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['name', 'group_type', 'balance'];

    // An account has many entries in the ledger
    public function entries()
    {
        return $this->hasMany(VoucherEntry::class);
    }
}