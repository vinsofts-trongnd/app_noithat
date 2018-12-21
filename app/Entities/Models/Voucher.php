<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $table = 'voucher_app';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'image',
        'icon',
        'number_point',
        'date_start',
        'date_end',
        'conditions',
        'description',
        'number_voucher',
        'remaining_voucher',
    ];

    public function codeVouchers()
    {
        return $this->hasMany(CodeVoucher::class, 'voucher_app_id', 'id');
    }
}
