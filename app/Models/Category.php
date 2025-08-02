<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function subcategories()
    {
        return $this->hasMany(SubCategory::class,'category_id');
    }

    public function activeSubcategories()
    {
        return $this->hasMany(SubCategory::class,'category_id')->where('status',1);
    }

    public function products()
    {
        return $this->hasMany(Product::class,'category_id')->where('status',1);
    }
}
