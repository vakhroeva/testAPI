<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = [
        'income_id',
        'number',
        'date',
        'last_change_date',
        'supplier_article',
        'tech_size',
        'barcode',
        'quantity',
        'total_price',
        'date_close',
        'warehouse_name',
        'nm_id',
    ];

    public function setDateAttribute($value): void
    {
        $this->attributes['date'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function setLastChangeDateAttribute($value): void
    {
        $this->attributes['last_change_date'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function setDateCloseAttribute($value): void
    {
        $this->attributes['date_close'] = Carbon::parse($value)->format('Y-m-d');
    }
}
