<?php

namespace App\Models;

use App\Models\Traits\BelongsToAcademy;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Counselor extends Model
{
    use HasFactory, HasUser, BelongsToAcademy;
}
