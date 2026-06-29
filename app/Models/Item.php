<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    // Added 'item_group' to allow the new categorization
    protected $fillable = [
        'name', 
        'category', 
        'item_group', 
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