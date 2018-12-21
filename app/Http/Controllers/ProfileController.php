<?php

namespace App\Http\Controllers;

use App\Entities\Models\Config;
use App\Entities\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Constants\ResponseStatusCode;
use App\Services\Upload\UploadService;
use App\Constants\Images;
use App\Entities\Models\ProfileService;
use Carbon\Carbon;
use App\Constants\Notification;
use App\Constants\ProfileType;
use App\Constants\Setting;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\ProfileDetailResource;
use JWTAuth;
use App\Constants\User as UserConstants;
use App\Entities\Models\Recruitment;
use App\Http\Resources\ProfileNoticeResource;

class ProfileController extends Controller
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
     * Api Create Profile.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'        => 'required|numeric',
            'type'           => 'required|numeric',
            'experience_id'  => 'required',
            'desired_salary' => 'required',
            'position'       => 'required',
        ]);
        if ($validator->fails()) {
            return $this->responseJson([
                'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'messages' => $validator->errors()->all(),
            ]);
        }
        $profile = Profile::create([
            'user_id'             => $request->user_id,
            'type'                => $request->type,
            'experience_id'       => $request->experience_id,
            'position'            => $request->position,
            'current_level_id'    => $request->current_level_id,
            'desired_level_id'    => $request->desired_level_id,
            'current_salary'      => $request->current_salary,
            'desired_salary'      => $request->desired_salary,
            'lat_lng_current'     => $request->current_workplace['lat_lng'],
            'address_current'     => $request->current_workplace['address_name'],
            'city_current'        => $request->current_workplace['city_name'],
            'lat_lng_desired'     => $request->desired_workplace['lat_lng'],
            'address_desired'     => $request->desired_workplace['address_name'],
            'city_desired'        => $request->desired_workplace['city_name'],
            'skill'               => $request->skill,
            'ability_overtime_id' => $request->ability_overtime_id,
            'ability_manage'      => $request->ability_manage,
            'ability_workfar'     => $request->ability_workfar,
            'status_notice'       => Notification::UNSENT_NOTIFICATION,
        ]);

        if ($request->image_product) {
            $this->uploadService->uploadImageProfile($profile->id,
                $request->input('image_product'), Images::IMAGE_PRODUCT);
        }

        if ($request->image_forte) {
            $this->uploadService->uploadImageProfile($profile->id,
                $request->input('image_forte'), Images::IMAGE_FORTE);
        }

        if ($request->image_drawing) {
            $this->uploadService->uploadImageProfile($profile->id,
                $request->input('image_drawing'), Images::IMAGE_DRAWING);
        }
        foreach ($request->service_id as $v) {
            ProfileService::create([
                'service_id' => $v,
                'profile_id' => $profile->id,
            ]);
        }
        $dateS = Carbon::now()->startOfMonth()->subMonth(6);
        $dateE = Carbon::now()->startOfMonth()->addMonth(1);
        $recruitment = Recruitment::with('services')
            ->whereBetween('created_at', [$dateS, $dateE])
            ->get();

        $config=Config::first();

        foreach ($recruitment as $value) {
            $percent = 0;
            if ($value->city_name == $profile->city_desired) {
                $percent = $config->percent;
            }
            if ($value->wage_max >= $profile->desired_salary) {
                $percent=$percent + $config->percent;
            }
            if ($value->services()->first()->id == $profile->services()->first()->id) {
                $percent=$percent + $config->percent;
            }
            if($percent >= 50){
                $data = [
                        'type'    => UserConstants::TYPE_NOTICE_HAVE_PROFILE_SATISFY,
                        'profile' => new ProfileNoticeResource($profile),
                    ];
                notification('Có Một Số Hồ Sơ Phù Hợp Với Yêu Cầu Của Bạn. Truy Cập LimberNow Ngay!', $value->id_user, $data);
            }
        }

        return $this->responseJson([
            'code'     => ResponseStatusCode::OK,
            'messages' => 'Create profile successful',
        ]);
    }

    /**
     * Get profile details.
     *
     * @param  id_profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfileDetail(Request $request, $id_profile)
    {
        $profile = Profile::with('user', 'services', 'experience', 'level_current',
            'level_desired','ability_overtime')->findOrFail($id_profile);

        if (!$profile) {
            return response()->json([
                'code' => ResponseStatusCode::NOT_FOUND,
                'data' => [
                    'message' => 'User not found',
                ],
            ], ResponseStatusCode::OK);
        }

        return $this->responseJson([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages' => 'Get profile details successful',
                'profiles' => new ProfileDetailResource($profile),
            ],
        ]);
    }

    /**
     * Get list profile.
     *
     * @param  $request
     * @param  $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile(Request $request, $type)
    {
        $searchProfile = $request->search;
        $service = explode(',', $request->service_id);
        $salary_max = $request->salary_max;
        $salary_min = $request->salary_min;
        $addressName = $request->address_name;
        $page = $request->page;

        $limit = Setting::DATA_LIMIT;
        $offset = $page * Setting::DATA_LIMIT;

        $profiles = Profile::with('services', 'user');

        if ($type == ProfileType::TYPE_BASIC_WORKER) {
            $profiles = $profiles->whereIn('type', [ProfileType::TYPE_BASIC_WORKER, ProfileType::TYPE_ADVANCED_WORKER]);
        } elseif($type == ProfileType::TYPE_STUDENT) {
            $profiles = $profiles->where('type', $type);
        }

        $profiles = $profiles
            ->when($searchProfile, function ($query, $searchProfile) {
                return $query->where(function ($q) use ($searchProfile) {
                    $q->where('position', 'like', '%' . $searchProfile . '%')
                        ->orWhereHas('user', function ($q1) use ($searchProfile) {
                            $q1->where('name', 'like', '%' . $searchProfile . '%');
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
            ->when($salary_max, function ($query, $salary_max) {
                return $query->where('desired_salary', '<=', $salary_max);
            })
            ->when($salary_min, function ($query, $salary_min) {
                return $query->where('desired_salary', '>=', $salary_min);
            })
            ->when($addressName, function ($query, $addressName) {
                return $query->where('city_desired', 'like', '%' . $addressName . '%');
            })->offset($offset)->limit($limit)->orderBy('updated_at', 'desc')->get();

        return $this->responseJson([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages' => 'Get profile successful',
                'profiles' => ProfileResource::collection($profiles),
            ],
        ]);
    }

    /**
     * API Notice Recruitment
     */
    public function noticeWorker()
    {
        $daynow = Carbon::now('Asia/Ho_Chi_Minh');
        $config = Config::first();
        $profile = Profile::where('status_notice', 0)
                ->where('created_at', '<=', $daynow->subDays($config->day_notice)
                ->format('Y-m-d H:i:s'))
                ->get();
        $userdupe = [];

        foreach ($profile as $index => $t) {
            if (isset($userdupe[$t["user_id"]])) {
                unset($profile[$index]);
                continue;
            }
            $userdupe[$t["user_id"]] = true;
        }

        foreach ($profile as $value) {

            $data = [
                'type'    => UserConstants::TYPE_NOTICE_ONE_WEEK_PROFILE,
                'profile' => new ProfileNoticeResource($value),
            ];
            notification('Bạn Đã Tìm Được Việc Làm Mong Muốn Chưa? Có Một Số Tin Tuyển Dụng Gần Đây Phù Hợp Với Mong Muốn Của Bạn!',
                $value->user_id, $data);
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
     * Get list profile coordinate.
     *
     * @param  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListProfileCoordinate(Request $request)
    {
        $addressLatLng = $request->lat_lng;
        $config = Config::first();
        $radius = $config->radius;

        $profiles = [];
        Profile::whereMonth('updated_at', '>=',
            Setting::CHECK_MONTH)->orderBy('updated_at', 'desc')->get()->map(function ($item) use (&$profiles, $addressLatLng, $radius) {
            if ($addressLatLng) {
                $km = distanceCalculation($addressLatLng, $item->lat_lng_desired);
                $item['km'] = $km;
                if ($km <= $radius) {
                    $profiles[] = new ProfileResource($item);
                }
            }
        });

        return $this->responseJson([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages' => 'Get profile coordinate successful',
                'profiles' => $profiles,
            ],
        ]);

    }

    /**
     * Get List Profile Of Me.
     *
     * @param  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfileOfMe($id)
    {
        $profile = Profile::with('services', 'experience')
            ->where('user_id', $id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'message' => 'Get list profile of me successful',
                'profile' => ProfileResource::collection($profile),
            ],
        ]);
    }

    /**
     * Update Profile Of Me.
     *
     * @param  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request, $id)
    {
        $profile = Profile::findOrFail($id);
        $profile->update($request->except('service_id', 'current_workplace', 'desired_workplace', 'image_product',
            'image_forte', 'image_drawing'));

        $profile->services()->sync($request->service_id);
        if ($request->current_workplace) {
            $profile->update([
                'lat_lng_current' => $request->current_workplace['lat_lng'],
                'address_current' => $request->current_workplace['address_name'],
                'city_current'    => $request->current_workplace['city_name'],
            ]);
        }

        if ($request->desired_workplace) {
            $profile->update([
                'lat_lng_desired' => $request->desired_workplace['lat_lng'],
                'address_desired' => $request->desired_workplace['address_name'],
                'city_desired'    => $request->desired_workplace['city_name'],
            ]);
        }

        if ($request->image_product) {
            $this->uploadService->uploadImageProfile($profile->id,
                $request->input('image_product'), Images::IMAGE_PRODUCT);
        }

        if ($request->image_forte) {
            $this->uploadService->uploadImageProfile($profile->id,
                $request->input('image_forte'), Images::IMAGE_FORTE);
        }

        if ($request->image_drawing) {
            $this->uploadService->uploadImageProfile($profile->id,
                $request->input('image_drawing'), Images::IMAGE_DRAWING);
        }

        $profile->update([
            'updated_at' => Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'message' => 'Update profiles successful',
                'profile' => new ProfileResource($profile),
            ],
        ]);
    }

    /**
     * Delete Profile.
     *
     * @param  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteProfile($id)
    {
        $profile = Profile::FindorFail($id);
        $profile->delete();
        return response()->json([
            'code'    => ResponseStatusCode::OK,
            'message' => "Delete profile successful",
        ]);
    }

    /**
     * Get concentrate profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConcentrateProfile()
    {
        $profile = Profile::whereRaw("city_desired = (SELECT COALESCE(city_desired) as CityDesired FROM profiles GROUP BY city_desired ORDER BY Count(*) DESC LIMIT 0, 1)")->get();

        if (count($profile) < Setting::MIN_OF_ITEM_FOR_CITY) {
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
                'messages' => 'Get concentrate profile successful',
                'concentrate_profile' => $profile[0]->city_desired,
            ],
        ]);

    }
}
