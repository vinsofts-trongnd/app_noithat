<?php

namespace App\Transformers;

use App\Entities\Models\CodeVoucher;
use League\Fractal\TransformerAbstract;

class CodeVoucherTransformer extends TransformerAbstract
{
    /**
     * List of Voucher possible to include
     *
     * @var array
     */
    protected $availableIncludes = ['voucher'];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(CodeVoucher $codeVoucher)
    {
        return [
            'id'             => $codeVoucher->id,
            'code'           => $codeVoucher->code,
            'status'         => $codeVoucher->status,
            'user_id'        => $codeVoucher->user_id,
            'voucher_app_id' => $codeVoucher->voucher_app_id,
            'date_used'      => $codeVoucher->date_used,
            'created_at'     => $codeVoucher->created_at,
            'updated_at'     => $codeVoucher->updated_at,
        ];
    }

    public function includeVoucher(CodeVoucher $codeVoucher)
    {
        $voucher = $codeVoucher->voucher;
        return $this->item($voucher, new VoucherTransformer);
    }
}
