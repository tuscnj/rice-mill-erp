<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    // Added 'opening_stock' and 'purchase_rate' to allow the initial setup values to be saved
    protected $fillable = [
        'name', 
        'category', 
        'unit', 
        'opening_stock', 
        'purchase_rate', 
        'current_stock', 
        'last_rate'
    ];

    // An item has many stock movements (in and out)
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}