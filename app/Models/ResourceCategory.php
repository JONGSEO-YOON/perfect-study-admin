<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceCategory extends Model
{
    use HasFactory;

    public function subCategories()
    {
        return $this->hasMany(ResourceSubCategory::class)
            ->orderBy('order', 'asc');
    }
}
