<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
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
        'is_supply',
        'is_realization',
        'promo_code_discount',
        'warehouse_name',
        'country_name',
        'oblast_okrug_name',
        'region_name',
        'income_id',
        'sale_id',
        'odid',
        'spp',
        'for_pay',
        'finished_price',
        'price_with_disc',
        'nm_id',
        'subject',
        'category',
        'brand',
        'is_storno',
        'account_id'
    ];

    public function setDateAttribute($value): void
    {
        $this->attributes['date'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function setLastChangeDateAttribute($value): void
    {
        $this->attributes['last_change_date'] = Carbon::parse($value)->format('Y-m-d');
    }
}
