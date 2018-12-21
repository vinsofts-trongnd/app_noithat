<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Entities\Models\Voucher;

class VoucherTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Voucher $voucher)
    {
        return [
            'id'                => $voucher->id,
            'image'             => $voucher->image,
            'icon'              => $voucher->icon,
            'name'              => $voucher->name,
            'number_point'      => $voucher->number_point,
            'date_start'        => $voucher->date_start,
            'date_end'          => $voucher->date_end,
            'conditions'        => $voucher->conditions,
            'description'       => $voucher->description,
            'remaining_voucher' => $voucher->remaining_voucher,
        ];
    }
}
