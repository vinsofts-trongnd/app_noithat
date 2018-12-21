<?php

namespace App\Http\Controllers;

use App\Components\Filesystem\Filesystem;
use App\Constants\App;
use App\Constants\ResponseStatusCode;
use App\Services\Upload\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class UploadController
 *
 * @package App\Http\Controllers
 */
class UploadController extends Controller
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
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function image(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images.*' => 'required|image|mimes:' . App::MIME_TYPE_IMAGE . '|max:' . App::IMAGE_MAXSIZE,
        ]);

        if ($validator->fails()) {
            return $this->responseJson([
                'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'messages' => $validator->errors()->all(),
            ]);
        }

        $images = $this->uploadService->image($request->file('images'));

        if (!empty($images)) {
            return $this->responseJson([
                'code' => ResponseStatusCode::OK,
                'data' => [
                    'images' => $images,
                ],
            ]);
        }

        return $this->responseJson([
            'code'     => ResponseStatusCode::BAD_REQUEST,
            'messages' => 'Upload fails!',
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function video(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'videos[]' => 'required'| App::MIME_TYPE_VIDEO . '|max:' . App::VIDEO_MAXSIZE,
        ]);

        if ($validator->fails()) {
            return $this->responseJson([
                'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'messages' => $validator->errors()->all(),
            ]);
        }

        $videos = $this->uploadService->videoUpload($request->file('videos'));

        if (!empty($videos)) {
            return $this->responseJson([
                'code' => ResponseStatusCode::OK,
                'data' => [
                    'videos' => $videos,
                ],
            ]);
        }

        return $this->responseJson([
            'code'     => ResponseStatusCode::BAD_REQUEST,
            'messages' => 'Upload fails!',
        ]);
    }
}
