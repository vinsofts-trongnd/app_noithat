<?php

namespace App\Http\Controllers;

use App\Entities\Models\Profile;
use App\Entities\Models\SaveJob;
use App\Http\Resources\ProfileResource;
use Illuminate\Http\Request;
use App\Constants\ResponseStatusCode;
use App\Entities\Models\Recruitment;
use App\Entities\Models\Wage;
use App\Entities\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Services\Upload\UploadService;
use Carbon\Carbon;
use App\Constants\Notification;
use App\Constants\User as UserConstants;
use App\Entities\Models\Config;
use App\Constants\Setting;
use App\Transformers\RecruitmentTransformer;
use App\Transformers\ConfigDataTransformer;
use App\Http\Resources\RecruitmentResource;
use JWTAuth;
use App\Entities\Models\DateCall;
use App\Http\Resources\DataCallResource;
use App\Http\Resources\RecruitmentNoticeResource;

class RecruitmentController extends Controller
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
     * API Recruitment
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function recruitment(Request $request)
    {
        $rules = [
            'id_user'             => 'required|integer',
            'vacancies'           => 'required',
            'number_vacancies'    => 'required|integer',
            'type'                => 'required|integer',
            'experience_id'       => 'required|integer',
            'description'         => 'required',
            'probationary_period' => 'integer',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseJson([
                'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'messages' => $validator->errors()->all(),
            ]);
        }

        $request->merge([
            'status_notice' => Notification::UNSENT_NOTIFICATION,
        ]);

        $recruitment = Recruitment::create($request->except('service_id'));

        if ($request->url_video) {

            $recruitment = $this->uploadService->uploadVideoFile($recruitment->id, $request->input('url_video'));
        }

        if ($request->url_image) {
            $recruitment = $this->uploadService->uploadImageFile($recruitment->id, $request->input('url_image'));
        }

        $recruitment->services()->sync($request->service_id);

        $dateS = Carbon::now()->startOfMonth()->subMonth(6);
        $dateE = Carbon::now()->startOfMonth()->addMonth(1);
        $profile = Profile::with('services')
            ->whereBetween('created_at', [$dateS, $dateE])
            ->get();

        $config = Config::first();

        foreach ($profile as $value) {
            $percent = 0;

            if ($value->city_desired == $recruitment->city_name) {
                $percent = $config->percent;
            }
            if ($value->desired_salary >= $recruitment->wage_min && $value->desired_salary <= $recruitment->wage_max) {
                $percent = $percent + $config->percent;
            }
            if ($value->services()->first()->id == $recruitment->services()->first()->id) {
                $percent = $percent + $config->percent;
            }

            if ($percent >= 50) {
                $data = [
                    'type'        => UserConstants::TYPE_NOTICE_HAVE_RECRUITMENT_SATISFY,
                    'recruitment' => new RecruitmentNoticeResource($recruitment),
                ];
                notification('Có Một Số Tin Tuyển Dụng Phù Hợp Với Yêu Cầu Của Bạn. Truy Cập LimberNow Ngay!',
                    $value->user_id, $data);
            }

        }
        $recruitment = fractal()->item($recruitment)->transformWith(new RecruitmentTransformer())->toArray();

        return $this->responseJson([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages'   => 'Congratulations! you have successfully posted your resume',
                'recruiment' => $recruitment,
                'service_id' => $request->service_id,
            ],
        ]);

    }

    /**
     * API Get Recruitment Me
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getRecruitment($id)
    {
        $recruitment = Recruitment::with('services')
            ->where('id_user', $id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'message'     => 'Get list recruitment successful',
                'recruitment' => RecruitmentResource::collection($recruitment),
            ],
        ]);

    }

    /**
     * API Update Recruitment Me
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function updateRecruitment(Request $request, $id)
    {
        $recruitment = Recruitment::findOrFail($id);
        $recruitment->update($request->except('service_id'));

        if ($request->url_video) {
            $recruitment = $this->uploadService->uploadVideoFile($recruitment->id, $request->input('url_video'));
        }

        if ($request->url_image) {
            $recruitment = $this->uploadService->uploadImageFile($recruitment->id, $request->input('url_image'));
        }

        $recruitment->services()->sync($request->service_id);
        $recruitment->update([
            'updated_at' => Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'message'     => 'Update recruitment successful',
                'recruitment' => $recruitment,
            ],
        ]);
    }

    /**
     * API Get Detail Recruitment
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getDetailRecruitment(Request $request, $id)
    {
        $recruitment = Recruitment::with('user', 'experience')->findOrFail($id);

        $recruitment = fractal()->item($recruitment)->transformWith(new RecruitmentTransformer($request->user_id))->serializeWith(new ConfigDataTransformer())->toArray();

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'message'     => 'Get detail recruitment successful',
                'recruitment' => $recruitment,
            ],
        ]);
    }

    /**
     * API Notice Recruitment
     */
    public function noticeRecruitment()
    {
        $daynow = Carbon::now('Asia/Ho_Chi_Minh');
        $config = Config::first();
        $recruitment = Recruitment::with('services', 'user')
            ->where('status_notice', 0)
            ->where('created_at', '<=', $daynow->subDays($config->day_notice)
                ->format('Y-m-d H:i:s'))
            ->get();
        $userdupe = [];

        foreach ($recruitment as $index => $t) {
            if (isset($userdupe[$t["id_user"]])) {
                unset($recruitment[$index]);
                continue;
            }
            $userdupe[$t["id_user"]] = true;
        }
        $data = RecruitmentNoticeResource::collection($recruitment);
        foreach ($data as $value) {

            $data = [
                'type'        => UserConstants::TYPE_NOTICE_ONE_WEEK_RECRUITMENT,
                'recruitment' => $value,
            ];
            notification('Bạn Đã Tìm Được Hồ Sơ Mong Muốn Chưa? Có Một Số Hồ Sơ Gần Đây Phù Hợp Với Yêu Cầu Của Bạn!',
                $value->id_user, $data);
            $value->update([
                'status_notice' => Notification::SENT_NOTIFICATION,
            ]);
        }
        return response()->json([
            'code'    => ResponseStatusCode::OK,
            'message' => 'Send notification successful',
        ]);
    }


    /**
     * API Call Now
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function callNow(Request $request, $id)
    {
        $user = User::FindorFail($id);
        $config = Config::first();

        if ($user->point <= $config->point_call) {
            return response()->json([
                'code'    => ResponseStatusCode::NOT_ENOUGH_POINT,
                'message' => 'You do not have enough points to call!',
            ]);
        }
        $user->update([
            'point' => $user->point - $config->point_call,
        ]);
        DateCall::create([
            'user_id'        => $user->id_users,
            'recruitment_id' => $request->recruitment_id,
            'profile_id'     => $request->profile_id,
            'user_receive'   => $request->user_receive,
            'status_notice'  => Notification::UNSENT_NOTIFICATION,
        ]);
        return response()->json([
            'code'    => ResponseStatusCode::OK,
            'message' => 'Call successfully!',
        ]);
    }

    /**
     * API Notice One Day
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function notice()
    {
        $daynow = Carbon::now();
        $config = Config::first();
        $call = DateCall::with('user')->where('status_notice', 0)
            ->where('created_at', '<=', $daynow->subDays($config->day_notice_call)
                ->format('Y-m-d H:i:s'))
            ->get();
        $userdupe = [];

        foreach ($call as $index => $t) {
            if (isset($userdupe[$t["user_id"]])) {
                unset($call[$index]);
                continue;
            }
            $userdupe[$t["user_id"]] = true;
        }

        foreach ($call as $v) {
            $data = [
                'type' => UserConstants::TYPE_CALL_NOW,
                'info' => $v,
            ];
            $name = $v->user()->first()->name;
            notification('Bạn Đã Liên Hệ Với ' . $name . ' Chưa?', $v->user_id, $data);
            $v->update([
                'status_notice' => Notification::SENT_NOTIFICATION,
            ]);
        }

        return response()->json([
            'code'    => ResponseStatusCode::OK,
            'message' => 'Send notification successful',
        ]);
    }

    /**
     * Get list recruitment.
     *
     * @param  $request
     * @param  $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListRecruitment(Request $request, $type)
    {
        $searchRecruitment = $request->search;
        $service = explode(',', $request->service_id);
        $wageMin = $request->wage_min;
        $wageMax = $request->wage_max;
        $addressName = $request->address_name;
        $addressLatLng = $request->lat_lng;
        $page = $request->page;

        $limit = Setting::DATA_LIMIT;
        $offset = $page * Setting::DATA_LIMIT;

        $recruitments = Recruitment::with('user', 'services')->where('type', $type);

        if ($type == Setting::TYPE_ALL) {
            $recruitments = Recruitment::with('user', 'services');
        }

        if ($wageMin && $wageMax) {
            $recruitments = $recruitments->whereRaw("(wage_min BETWEEN " . $wageMin . " AND " . $wageMax . " OR wage_max BETWEEN " . $wageMin . " AND " . $wageMax . "
                                                    OR " . $wageMin . " BETWEEN wage_min AND wage_max OR " . $wageMax . " BETWEEN wage_min AND wage_max)");
        }

        $recruitments = $recruitments
            ->when($searchRecruitment, function ($query, $searchRecruitment) {
                return $query->where(function ($q) use ($searchRecruitment) {
                    $q->where('vacancies', 'like', '%' . $searchRecruitment . '%')
                        ->orWhereHas('user', function ($q1) use ($searchRecruitment) {
                            $q1->where('name', 'like', '%' . $searchRecruitment . '%');
                        });
                });
            })
            ->when($service, function ($query, $service) {
                if ($service[0] != null) {
                    return $query->whereHas('services', function ($q) use ($service) {
                        $q->whereIn('service.id', $service);
                    });
                }
            })
            ->when($addressName, function ($query, $addressName) {
                return $query->where('city_name', 'like', '%' . $addressName . '%');
            })->offset($offset)->limit($limit)->orderBy('updated_at', 'desc')->get();

        foreach ($recruitments as $item) {
            if ($addressLatLng) {
                $km = distanceCalculation($addressLatLng, $item->work_location_lat_lng);
                $item->km = round($km, 1);
            }
        };

        $recruitmentTransformer = fractal()->collection($recruitments)->transformWith(new RecruitmentTransformer($request->user_id))->serializeWith(new ConfigDataTransformer())->toArray();

        return $this->responseJson([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages'     => 'Get list recruitment successful',
                'recruitments' => $recruitmentTransformer['data'],
            ],
        ]);
    }

    /**
     * Get list recruitment coordinate.
     *
     * @param  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListRecruitmentCoordinate(Request $request)
    {
        $addressLatLng = $request->lat_lng;
        $config = Config::first();
        $radius = $config->radius;

        $recruitments = [];
        Recruitment::whereMonth('updated_at', '>=',
            Setting::CHECK_MONTH)->get()->map(function ($item) use (&$recruitments, $addressLatLng, $radius) {
            if ($addressLatLng) {
                $km = distanceCalculation($addressLatLng, $item->work_location_lat_lng);
                $item['km'] = $km;
                if ($km <= $radius) {
                    $recruitments[] = $item;
                }
            }
        });

        $recruitmentTransformer = fractal()->collection($recruitments)->transformWith(new RecruitmentTransformer($request->user_id))->serializeWith(new ConfigDataTransformer())->toArray();

        return $this->responseJson([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages'     => 'Get recruitments coordinate successful',
                'recruitments' => $recruitmentTransformer['data'],
            ],
        ]);

    }

    /**
     * API Delete Recrument
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function deleteRecruitment($id)
    {
        $recruitment = Recruitment::FindorFail($id);
        $recruitment->delete();
        return response()->json([
            'code'    => ResponseStatusCode::OK,
            'message' => 'Delete recruitment successful',
        ]);
    }

    /**
     * Get concentrate recruitment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConcentrateRecruitment()
    {
        $recruitment = Recruitment::whereRaw("city_name = (SELECT COALESCE(city_name) as CityName FROM recruitment GROUP BY city_name ORDER BY Count(*) DESC LIMIT 0, 1)")->get();

        if (count($recruitment) < Setting::MIN_OF_ITEM_FOR_CITY) {
            return $this->responseJson([
                'code' => ResponseStatusCode::NO_RESULT,
                'data' => [
                    'messages' => 'No result',
                ],
            ]);
        }

        return $this->responseJson([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages'                => 'Get concentrate recruitment successful',
                'concentrate_recruitment' => $recruitment[0]->city_name,
            ],
        ]);

    }
}
