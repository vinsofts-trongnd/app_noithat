<?php

namespace App\Http\Controllers;

use App\Constants\ResponseStatusCode;
use App\Constants\VoucherStatus;
use App\Entities\Models\User;
use App\Entities\Models\Voucher;
use App\Entities\Models\CodeVoucher;
use App\Http\Resources\UserResource;
use App\Services\Upload\UploadService;
use App\Entities\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Constants\User as UserConstants;
use JWTAuth;
use App\Constants\UserRole;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * @var UploadService
     */
    private $uploadService;

    /**
     * UploadController constructor.
     *
     * @param UploadService $uploadService
     */
    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * API register with phone
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $rules = [
            'name'             => 'regex:/[a-zA-Z0-9\sàáạã_-]{2,32}/',
            'phone'            => 'required|unique:users,username|digits_between:9,12|numeric',
            'password'         => 'required|min:5',
            'confirm_password' => 'required|same:password',
        ];

        if ($request->input('email')) {
            $rules['email'] = 'email';
        };
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseJson([
                'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'messages' => $validator->errors()->all(),
            ]);
        }
        if ($request->code_intro) {
            $user_present = User::where('code_present', $request->code_intro)->first();

            if (!empty($user_present)) {
                $config = Config::first();
                $code = $this->randomcode();
                $user = User::create([
                    'name'         => trim($request->input('name')),
                    'username'     => $request->input('phone'),
                    'telephone'    => $request->input('phone'),
                    'code_intro'   => $request->input('code_intro'),
                    'password'     => md5($request->input('password')),
                    'code_present' => $code,
                    'point'        => $config->ponint,
                ]);
                $user_present->update([
                    'point' => (int)$user_present->point + $config->ponint,
                ]);
                $config = Config::first();
                $data = [
                    "type"  => UserConstants::TYPE_CODEINTRO,
                    "point" => $config->point,
                ];
                notification('Có người nhập mã giới thiệu của bạn. Chúc mừng bạn đã được cộng điểm',
                    $user_present->id_users, $data);

                $user = User::find($user->id_users);
                $token = JWTAuth::fromUser($user);

                return $this->responseJson([
                    'code' => ResponseStatusCode::OK,
                    'data' => [
                        'token'   => $token,
                        'message' => 'Create successful',
                        'user'    => $user,
                    ],
                ]);
            }
            return response()->json([
                'code'    => ResponseStatusCode::CODE_PRESENT_NOT_EXISTS,
                'message' => 'Code_present does not exists',
            ]);
        } else {
            $code = $this->randomcode();
            $user = User::create([
                'name'         => trim($request->input('name')),
                'username'     => $request->input('phone'),
                'telephone'    => $request->input('phone'),
                'code_intro'   => $request->input('code_intro'),
                'password'     => md5($request->input('password')),
                'code_present' => $code,
            ]);
            $user = User::find($user->id_users);
            $token = JWTAuth::fromUser($user);
            return $this->responseJson([
                'code' => ResponseStatusCode::OK,
                'data' => [
                    'token'   => $token,
                    'message' => 'Create successful',
                    'user'    => $user,
                ],
            ]);
        }
    }

    /**
     * Function randomcode
     *
     * @return string
     *
     */
    public function randomcode()
    {
        $code = str_random(8);
        $code_presents = User::where('code_present', $code)->count();
        if ($code_presents > 0) {
            randomcode($code);
        } else {
            return $code;
        }
    }

    /**
     * API login with social
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginSocial(Request $request)
    {
        $rules = [
            'name'  => 'required',
            'phone' => 'digits_between:9,12|numeric',
            'type'  => 'required|integer',
        ];

        if ($request->input('email')) {
            $rules['email'] = 'email';
        };
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseJson([
                'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'messages' => $validator->errors()->all(),
            ]);
        }
        $id_social = $request->type == UserConstants::TYPE_FACEBOOK ? 'idFB' : 'idGG';
        $user = User::where($id_social, $request->access_token)->first();

        if (empty($user)) {
            $code = $this->randomcode();
            $phone = User::where('username', $request->phone)->first();
            if (!empty($phone)) {
                return response()->json([
                    'code'    => ResponseStatusCode::PHONE_NOT_FOUND,
                    'message' => "Phone numbers are in the system",
                ]);
            }
            $user = User::create([
                'name'         => trim($request->input('name')),
                'username'     => $request->input('phone'),
                'telephone'    => $request->input('phone'),
                'email'        => $request->input('email'),
                'password'     => md5($request->input('access_token')),
                'idFB'         => $request->input('type') == UserConstants::TYPE_FACEBOOK ? $request->input('access_token') : '',
                'idGG'         => $request->input('type') == UserConstants::TYPE_GOOGLE ? $request->input('access_token') : '',
                'code_present' => $code,
            ]);
            $user = User::find($user->id_users);
        }

        $token = JWTAuth::fromUser($user);

        return $this->responseJson([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'token'    => $token,
                'messages' => 'Login successful',
                'user'     => $user,
            ],
        ]);

    }

    /**
     * API login
     *
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone'    => 'required|numeric|digits_between:9,12',
            'password' => 'required|min:5',
        ]);
        if ($validator->fails()) {
            return $this->responseJson([
                'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'messages' => $validator->errors()->all(),
            ]);
        }

        try {
            $user = User::where('username', $request->phone)
                ->where('password', md5($request->password))
                ->first();

            if (empty($user)) {
                return $this->responseJson([
                    'code'     => ResponseStatusCode::NOT_FOUND,
                    'messages' => 'Invalid Phone or Password',
                ]);
            }

            $token = JWTAuth::fromUser($user);

            return $this->responseJson([
                'code' => ResponseStatusCode::OK,
                'data' => [
                    'token' => $token,
                    'user'  => $user,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->responseJson([
                'code'     => ResponseStatusCode::NOT_FOUND,
                'messages' => [$e->getMessage()],
            ], ResponseStatusCode::NOT_FOUND);
        }
    }

    /**
     * API CheckPhone
     *
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits_between:9,12|numeric',
        ]);
        if ($validator->fails()) {
            return $this->responseJson([
                'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'messages' => $validator->errors()->all(),
            ]);
        }
        $phoneUser = User::where('username', $request->phone)->first();

        if (!empty($phoneUser)) {
            if ($phoneUser->idFB || $phoneUser->idGG) {
                $name_social = $phoneUser->idFB == UserConstants::TYPE_FACEBOOK ? 'Facebook' : 'Google';
                return response()->json([
                    'code'    => ResponseStatusCode::CHECK_PHONE_SOCIAL,
                    'message' => 'Phone numbers are in the system, the phone number had register with ' . $name_social,
                ]);
            }
            return response()->json([
                'code'    => ResponseStatusCode::OK,
                'message' => 'Phone numbers are in the system',
            ]);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::NOT_FOUND,
                'message' => 'Phone does not exists',
            ]);
        }
    }

    /**
     * API Forgot Password
     *
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone'    => 'required|digits_between:9,12|numeric',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return $this->responseJson([
                'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'messages' => $validator->errors()->all(),
            ]);
        }
        $user = User::where('username', $request->phone)->first();
        if (empty($user)) {
            return response()->json([
                'code'    => ResponseStatusCode::NOT_FOUND,
                'message' => 'User does not exists',
            ]);
        } else {
            $user->update([
                'password' => md5($request->password),
            ]);

            return response()->json([
                'code'    => ResponseStatusCode::OK,
                'message' => 'Change password successfull',
            ]);
        }
    }

    /**
     * API Check Token
     *
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'         => 'required|integer',
            'access_token' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->responseJson([
                'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'messages' => $validator->errors()->all(),
            ]);
        }

        $id_social = $request->type == UserConstants::TYPE_FACEBOOK ? 'idFB' : 'idGG';
        $user = User::where($id_social, $request->access_token)->first();

        if (empty($user)) {
            return response()->json([
                'code'    => ResponseStatusCode::NOT_FOUND,
                'message' => 'User does not exists',
            ]);
        }

        if (!$user->telephone) {
            return response()->json([
                'code'    => ResponseStatusCode::PHONE_NOT_FOUND,
                'message' => 'Phone does not exists',
            ], ResponseStatusCode::OK);
        }
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'token'   => $token,
                'message' => 'Get user successfull',
                'user'    => $user,
            ],
        ]);
    }

    /**
     * API update phone
     *
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function updatePhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'         => 'required|integer',
            'phone'        => 'required|unique:users,username|numeric|digits_between:9,12',
            'access_token' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->responseJson([
                'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'messages' => $validator->errors()->all(),
            ], ResponseStatusCode::OK);
        }

        $id_social = $request->type == UserConstants::TYPE_FACEBOOK ? 'idFB' : 'idGG';
        $user = User::where($id_social, $request->access_token)->first();
        if (!empty($user)) {
            $user->update([
                'username'  => $request->input('phone'),
                'telephone' => $request->input('phone'),
            ]);
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => [
                    'token'   => $token,
                    'message' => 'Update phone successfull',
                    'user'    => $user,
                ],
            ], ResponseStatusCode::OK);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::LENGTH_REQUIRED,
                'message' => 'Update phone fail',
            ], ResponseStatusCode::OK);
        }
    }

    /**
     * API logut
     *
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $token = JWTAuth::getToken();
        JWTAuth::invalidate($token);
        return response()->json([
            'code'    => ResponseStatusCode::OK,
            'message' => 'Logout successfull',
        ], ResponseStatusCode::OK);
    }

    /**
     * API Get Role User
     *
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoleUser(Request $request)
    {
        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'message'   => 'Get role user info successful',
                'role_user' => UserRole::USER_ROLE,
            ],
        ]);
    }

    /**
     * API Update Info User
     *
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function upgradeAccount(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'type'          => 'required|numeric',
                'name'          => 'required',
                'address'       => 'required',
                'service'       => 'required',
                'identity_card' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return $this->responseJson([
                    'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                    'messages' => $validator->errors()->all(),
                ], ResponseStatusCode::OK);
            }

            $user = User::where('id_users', $request->id_users)->first();

            if (!empty($user)) {
                $user->update([
                    'name'          => $request->name,
                    'address'       => $request->address,
                    'service'       => json_encode($request->service),
                    'identity_card' => $request->identity_card,
                    'type_temp'     => $request->type,
                ]);

                if ($request->input('images')) {
                    $this->uploadService->uploadImageUpgradeUser($request->id_users,
                        $request->input('images'));
                }

                return response()->json([
                    'code' => ResponseStatusCode::OK,
                    'data' => [
                        'message' => 'Upgrade account successful',
                        'user'    => $user,
                    ],
                ]);
            }
            return response()->json([
                'code'    => ResponseStatusCode::NOT_FOUND,
                'message' => "User does not exists",
            ]);

        } catch (Exception $e) {
            return response()->json([
                'code'     => ResponseStatusCode::BAD_REQUEST,
                'messages' => [$e->getMessage()],
            ]);
        }
    }

    /**
     * API Update Code Intro
     *
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function putCodeIntro(Request $request)
    {
        try {
            $valicodeintro = Validator::make($request->all(), [
                'code_intro' => 'required',
            ]);

            if ($valicodeintro->fails()) {
                return $this->responseJson([
                    'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                    'messages' => $valicodeintro->errors()->all(),
                ], ResponseStatusCode::OK);
            }

            $user = User::where('id_users', $request->id_users)->first();

            if (isset($user)) {

                if($user->code_intro){
                    return response()->json([
                        'code' => ResponseStatusCode::HAVE_CODE_INTRO,
                        'message' => "You already have code intro",
                    ]);
                }
                if (strtolower($request->code_intro) == strtolower($user->code_present)) {
                    return response()->json([
                        'code'    => ResponseStatusCode::CODE_INTRO_ERROR,
                        'message' => "You do not have to enter your own code",
                    ]);
                }
                $user_present = User::where('code_present', $request->code_intro)->first();
                $config = Config::first();

                if (!empty($user_present)) {

                    $user->update([
                        'code_intro' => $request->code_intro,
                        'point'      => (int)$user->point + $config->point,
                    ]);
                    $user_present->update([
                        'point' => (int)$user_present->point + $config->point,
                    ]);

                    $config = Config::first();
                    $data = [
                        "type"  => UserConstants::TYPE_CODEINTRO,
                        "point" => $config->point,
                    ];
                    notification('Có người nhập mã giới thiệu của bạn. Chúc mừng bạn đã được cộng điểm',
                        $user_present->id_users, $data);

                    return response()->json([
                        'code' => ResponseStatusCode::OK,
                        'data' => [
                            'message' => 'Update code intro successfull',
                            'user'    => $user,
                        ],
                    ]);
                } else {
                    return response()->json([
                        'code'    => ResponseStatusCode::CODE_PRESENT_NOT_EXISTS,
                        'message' => 'Code present does not exists',
                    ]);
                }
            }
            return response()->json([
                'code'    => ResponseStatusCode::NOT_FOUND,
                'message' => "User does not exists",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'code'     => ResponseStatusCode::BAD_REQUEST,
                'messages' => [$e->getMessage()],
            ]);
        }
    }

    /**
     * API test Upgrade User
     *
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testUpgradeUser(Request $request)
    {
        $user = User::where('id_users', $request->id_users)->first();
        $config = Config::first();
        if (!$user) {
            return response()->json([
                'code'    => ResponseStatusCode::NOT_FOUND,
                'message' => 'User not found',
            ], ResponseStatusCode::OK);
        } else {
            $user->save([
                $user->point    = $config->point_upgrade + $user->point,
                $user->rate     = UserConstants::RATE_USERS,
                $user->loaitk   = $user->type_temp,
            ]);

            $data = [
                "type" => UserConstants::TYPE_UPGRADEUSER,
                "user" => $user,
            ];

            $userRoles = UserRole::USER_ROLE;

            foreach ($userRoles as $item) {
                if ($item['type'] == $user->loaitk) {
                    $nameUserRole = $item['name'];
                }
            }

            notification('Chúc mừng bạn đã nâng cấp tài khoản lên ' .$nameUserRole.' .Hãy truy cập ngay để trải nghiệm LimBerNow!', $user->id_users, $data);
            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => [
                    'message' => 'Upgrade user successfull',
                    'user'    => $user,
                ],
            ], ResponseStatusCode::OK);
        }
    }

    /**
     * API Change Voucher
     *
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeVoucher(Request $request, $user_id)
    {
        $user = User::where('id_users', $request->id_users)->first();
        $vouchers = Voucher::where('id', $request->id)->first();

        if (isset($vouchers) && $vouchers->remaining_voucher > 0) {
            $datenow = Carbon::now(+7)->format('Y-m-d');

            if (strtotime($vouchers->date_end) >= strtotime($datenow)) {
                if ($user->point >= $vouchers->number_point) {
                    $result = $user->point - $vouchers->number_point;

                    $user->update([
                        $user->point = $result,
                    ]);
                    $vouchers->update([
                        'remaining_voucher' => (int)$vouchers->remaining_voucher - VoucherStatus::VOUCHER_USED,
                    ]);
                    $codeVoucher = CodeVoucher::where('user_id', null)->where('voucher_app_id', $request->id)->first();

                    if (!$codeVoucher) {
                        return response()->json([
                            'code' => ResponseStatusCode::NUMBER_CODE_EXPIRED,
                            'data' => [
                                'message' => 'Number of expired codes',
                            ],
                        ], ResponseStatusCode::OK);
                    }
                    $codeVoucher->update([
                        $codeVoucher->user_id = $request->id_users,
                        $codeVoucher->date_used = $datenow,
                    ]);
                    $vouchers = Voucher::find($vouchers->id);

                    return response()->json([
                        'code' => ResponseStatusCode::OK,
                        'data' => [
                            'message'      => 'Congratulation! You changed the voucher successfully!!',
                            'voucher_code' => $codeVoucher->code,
                            'voucher'      => $vouchers,
                        ],
                    ], ResponseStatusCode::OK);
                } else {
                    return response()->json([
                        'code' => ResponseStatusCode::NOT_ENOUGH_POINT,
                        'data' => [
                            'message' => 'You do not have enough points to change the Voucher',
                        ],
                    ], ResponseStatusCode::OK);
                }
            }
            return response()->json([
                'code' => ResponseStatusCode::VOUCHER_EXPIRED_DATE,
                'data' => [
                    'message' => 'Expiry date of voucher!',
                ],
            ], ResponseStatusCode::OK);
        }
        return response()->json([
            'code' => ResponseStatusCode::NUMBER_VOUCHER_EXPIRED,
            'data' => [
                'message' => 'Number of expired vouchers!',
            ],
        ], ResponseStatusCode::OK);
    }

    /**
     * API get info user
     *
     * @param $id_users
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getInfoUser($id_users)
    {
        $user = User::where('id_users', $id_users)->first();

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages'  => 'Get info user successful',
                'info_user' => new UserResource($user),
            ],
        ]);
    }

    /**
     * API update user
     *
     * @param $request , $id_users
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function updateUser(Request $request, $id_users)
    {
        $user = User::where('id_users', $id_users)->first();

        $user->update($request->all());

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'user' => [
                'messages' => 'Update user info successful',
                'user'     => $user
            ],
        ]);
    }
}
