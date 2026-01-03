<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Academy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'business_name',
        'business_number',
        'representative_name',
        'business_address',
        'business_phone',
        'contact_phone',
        'contact_email',
        'is_active',
        'memo',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getFormattedBusinessNumberAttribute(): ?string
    {
        if (!$this->business_number) {
            return null;
        }
        
        $num = preg_replace('/[^0-9]/', '', $this->business_number);
        if (strlen($num) === 10) {
            return substr($num, 0, 3) . '-' . substr($num, 3, 2) . '-' . substr($num, 5);
        }
        return $this->business_number;
    }
}
