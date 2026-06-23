<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = ['voucher_id', 'item_id', 'quantity', 'rate', 'movement_type'];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}