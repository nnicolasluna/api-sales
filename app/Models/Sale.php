<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'user_id',
        'total'
    ];
    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class, 'sale_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
