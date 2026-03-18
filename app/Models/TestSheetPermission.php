<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestSheetPermission extends Model
{
    protected $guarded = [];

    public function testSheet()
    {
        return $this->belongsTo(TestSheet::class);
    }

    public function academy()
    {
        return $this->belongsTo(Academy::class);
    }
}
