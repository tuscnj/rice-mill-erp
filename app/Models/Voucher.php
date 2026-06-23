<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = ['voucher_type', 'voucher_date', 'reference_number', 'notes'];

    // A voucher can have multiple financial debits/credits
    public function entries()
    {
        return $this->hasMany(VoucherEntry::class);
    }

    // A voucher can have multiple physical stock changes (like milling)
    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}