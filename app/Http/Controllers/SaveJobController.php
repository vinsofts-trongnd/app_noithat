<?php

namespace App\Http\Controllers;

use App\Constants\RecruitmentSaveJob;
use Illuminate\Http\Request;
use App\Entities\Models\SaveJob;
use Illuminate\Support\Facades\Validator;
use App\Constants\ResponseStatusCode;
use App\Constants\IsSaved;


class SaveJobController extends Controller
{
    /**
     * API Save Job
     *
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveJob(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'       => 'required|numeric',
            'saved_job_id'  => 'required|numeric',
            'is_saved'      => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->responseJson([
                'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'messages' => $validator->errors()->all(),
            ]);
        }

        if ($request->is_saved == IsSaved::SAVE) {
            $savejob = SaveJob::where('user_id',$request->user_id)->where('saved_job_id', $request->saved_job_id)->first();

            if (isset($savejob)) {
                return response()->json([
                    'code'    => ResponseStatusCode::ALREADY_EXIST_JOB,
                    'message' => 'Job already exist',
                ]);
            }
            SaveJob::create([
                'user_id'      => $request->user_id,
                'saved_job_id' => $request->saved_job_id,
            ]);
            return response()->json([
                'code'    => ResponseStatusCode::OK,
                'message' => 'Save job successful',
            ]);
        }

        $savejob = SaveJob::where('user_id',$request->user_id)->where('saved_job_id', $request->saved_job_id)->first();
        if(empty($savejob)){
            return response()->json([
                'code'=> ResponseStatusCode::NOT_FOUND,
                'message' => 'Job does not exist'
            ]);
        }
        $savejob->delete();

        return response()->json([
            'code'    => ResponseStatusCode::OK,
            'message' => 'Delete job successfully',
        ]);
    }

    /**
     * API Get Saved Job
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getJobSaved($id)
    {
        $saved_job = SaveJob::with('recruitment')
            ->where('user_id', $id)->get()
            ->map(function ($item) {

            if (isset($item->recruitment) && $item->recruitment) {
                $item->recruitment->save_status = RecruitmentSaveJob::SAVED;
                $item->recruitment->level_recruitment = $item->recruitment->level_recruitment_id;
                $item->recruitment->images = json_decode($item->recruitment->images);
                unset($item->recruitment->level_recruitment_id);
            }
            return $item;
        });

        return $this->responseJson([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages'     => 'Get list job saved successful!',
                'saved_job_id' => $saved_job,
            ],
        ]);
    }

    /**
     * API Delete Saved Job
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $saved_job = SaveJob::where('id', $id)->first();

        if (!$saved_job) {
            return response()->json([
                'code'    => ResponseStatusCode::NOT_FOUND,
                'message' => 'Job not found',
            ], ResponseStatusCode::OK);
        }

        $saved_job->delete();

        return $this->responseJson([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages'     => 'Delete saved job successful',
                'saved_job_id' => $saved_job->id,
            ],
        ]);
    }
}
