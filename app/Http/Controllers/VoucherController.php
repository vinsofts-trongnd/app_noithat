<?php

namespace App\Http\Controllers;

use App\Entities\Models\CodeVoucher;
use App\Entities\Models\Voucher;
use App\Transformers\CodeVoucherTransformer;
use App\Transformers\ConfigDataTransformer;
use Illuminate\Http\Request;
use App\Constants\ResponseStatusCode;
use Carbon\Carbon;

class VoucherController extends Controller
{
    /**
     * API get list voucher
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getListVoucher()
    {
        $dateNow = Carbon::now(+7)->format('Y-m-d');

        $vouchers = Voucher::where('remaining_voucher', '>', 0)->whereDate('date_end', '>=', $dateNow)->get();

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages' => 'Get list voucher successful',
                'vouchers' => $vouchers,
            ],
        ], ResponseStatusCode::OK);
    }

    /**
     * API get list history voucher
     *
     * @param $id_users
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getListHistoryVoucher($id_users)
    {
        $historyVouchers = CodeVoucher::where('user_id', $id_users)->get();

        $vouchers = fractal()->collection($historyVouchers)->parseIncludes('voucher')->transformWith(new CodeVoucherTransformer())->serializeWith(new ConfigDataTransformer())->toArray();

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages'      => 'Get list history voucher successful',
                'code_vouchers' => $vouchers['data'],
            ],
        ], ResponseStatusCode::OK);
    }
}
