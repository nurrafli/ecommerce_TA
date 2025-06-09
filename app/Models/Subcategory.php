<?php

namespace App\Models;

use App\Model\Category;
use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
   public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function parent()
    {
        return $this->belongsTo(Subcategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Subcategory::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

}
