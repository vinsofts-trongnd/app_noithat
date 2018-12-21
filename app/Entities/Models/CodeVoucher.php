<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class CodeVoucher extends Model
{
    protected $table = 'code_vouchers';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'status',
        'voucher_app_id',
        'user_id',
        'date_used',
    ]; 

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_app_id', 'id');
    }
}
