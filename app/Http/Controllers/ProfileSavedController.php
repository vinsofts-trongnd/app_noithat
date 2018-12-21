<?php

namespace App\Http\Controllers;

use App\Constants\Setting;
use Illuminate\Http\Request;
use App\Entities\Models\SaveProfile;
use Illuminate\Support\Facades\Validator;
use App\Constants\ResponseStatusCode;
use App\Constants\IsSaved;
use App\Services\Averagestar\AveragestarService;

class ProfileSavedController extends Controller
{
    /**
     * API Save Profile
     *
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profileSaved(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'    => 'required|numeric',
            'profile_id' => 'required|numeric',
            'is_saved'   => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->responseJson([
                'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'messages' => $validator->errors()->all(),
            ]);
        }

        if ($request->is_saved == IsSaved::SAVE) {
            $saveprofile = SaveProfile::where('user_id',$request->user_id)->where('profile_id', $request->profile_id)->first();

            if (isset($saveprofile)) {
                return response()->json([
                    'code'    => ResponseStatusCode::ALREADY_EXIST_PROFILE,
                    'message' => 'Profile already exist',
                ]);
            }
            SaveProfile::create([
                'user_id'    => $request->user_id,
                'profile_id' => $request->profile_id,
            ]);
            return response()->json([
                'code'    => ResponseStatusCode::OK,
                'message' => 'Save profile successful',
            ]);
        }

        $saveprofile = SaveProfile::where('user_id',$request->user_id)->where('profile_id', $request->profile_id)->first();
        if(empty($saveprofile)){
            return response()->json([
                'code'=> ResponseStatusCode::PROFILE_DOES_NOT_EXIST,
                'message' => 'Profile does not exist'
            ]);
        }
        $saveprofile->delete();

        return response()->json([
            'code'    => ResponseStatusCode::OK,
            'message' => 'Delete profile successfully',
        ]);

    }

    /**
     * API Get Profile Saved
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfileSaved($id)
    {
        $profileSaved = SaveProfile::with('user','profiles')->where('user_id', $id)->get()->map(function ($item) {
            if (isset($item->profiles) && $item->profiles) {
                $item->profiles->image_product = json_decode($item->profiles->image_product);
                $item->profiles->image_forte   = json_decode($item->profiles->image_forte);
                $item->profiles->image_drawing = json_decode($item->profiles->image_drawing);
            }

            if (isset($item->user) && $item->user) {
                $output = AveragestarService::rating($item->user->id_users);
                $item->user->rate_feedback = $output->result;
            }
            if (isset($item->user) && $item->user) {
                $item->user->count_comment = $output->count_comment;
            }

            return $item;
        });

        foreach ($profileSaved as $item) {
            $item['save_status'] = Setting::SAVED;
        }

        return $this->responseJson([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages'      => 'Get list profile saved successful',
                'profile_saved' => $profileSaved,
            ],
        ]);
    }

    /**
     * API Delete Profile Saved
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $profileSaved = SaveProfile::where('id', $id)->first();

        if (!$profileSaved) {
            return response()->json([
                'code'    => ResponseStatusCode::NOT_FOUND,
                'message' => 'Profile not found',
            ], ResponseStatusCode::OK);
        }
        $profileSaved->delete();

        return $this->responseJson([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages'   => 'Delete profile successful',
                'id' => $profileSaved->id,
            ],
        ]);
    }
}
