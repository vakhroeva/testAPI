<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'g_number',
        'date',
        'last_change_date',
        'supplier_article',
        'tech_size',
        'barcode',
        'total_price',
        'discount_percent',
        'warehouse_name',
        'oblast',
        'income_id',
        'odid',
        'nm_id',
        'subject',
        'category',
        'brand',
        'is_cancel',
        'cancel_dt',
        'account_id'
    ];

    public function setDateAttribute($value): void
    {
        $this->attributes['date'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function setLastChangeDateAttribute($value): void
    {
        $this->attributes['last_change_date'] = Carbon::parse($value)->format('Y-m-d');
    }
}
