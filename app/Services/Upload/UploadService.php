<?php

namespace App\Services\Upload;

use App\Components\Filesystem\Filesystem;
use App\Constants\Directory;
use App\Entities\Models\UpgradeImage;
use App\Entities\Models\Profile;
use App\Constants\Images;
use App\Entities\Models\Recruitment;


/**
 * Class UploadService
 *
 * @package App\Services\Upload
 */
class UploadService
{
    /**
     * @var Filesystem
     */
    private $fileUpload;

    /**
     * UploadService constructor.
     *
     * @param Filesystem $fileUpload
     */
    public function __construct(Filesystem $fileUpload)
    {
        $this->fileUpload = $fileUpload;
    }

    /**
     * @param \Illuminate\Http\UploadedFile[] $files
     *
     * @return array
     */
    public function image($files)
    {
        $images = $this->fileUpload->uploadTemp($files);
        $images = is_array($images) ? $images : [];

        return array_map(function ($img) {
            return url($img);
        }, $images);
    }

    /**
     * @param \Illuminate\Http\UploadedFile[] $images
     *
     */
    public function uploadImageUpgradeUser($id_users, $images)
    {
        if ($images) {
            // remove url exist
            UpgradeImage::where('user_id', $id_users)->delete();

            foreach ($images as $key => $item) {
                $path = $this->fileUpload->moveTempUpload(Directory::UPGRADE_IMAGE, $item['url']);

                if (empty($path)) {
                    continue;
                }

                UpgradeImage::create([
                    'user_id' => $id_users,
                    'image'   => $path ? $path : $item['url'],
                    // nếu update mà đã có ảnh trước và 1 ảnh mới thay đổi thì lấy path là url cũ
                    'type'    => $item['type'],
                ]);
            }
        }
    }

    /**
     * @param \Illuminate\Http\UploadedFile[] $images
     *
     */
    public function uploadImageProfile($id, $images, $type)
    {
        if ($images) {
            $pathimage = [];
            foreach ($images as $key => $item) {
                $path = $this->fileUpload->moveTempUpload(Directory::UPGRADE_IMAGE, $item);
                if (empty($path)) {
                    continue;
                }
                array_push($pathimage, $path);

            }
            $profile = Profile::find($id);
            if ($type == Images::IMAGE_PRODUCT) {
                $profile->update([
                    'image_product' => json_encode($pathimage),
                ]);
            }
            if ($type == Images::IMAGE_FORTE) {
                $profile->update([
                    'image_forte' => json_encode($pathimage),
                ]);
            }
            if ($type == Images::IMAGE_DRAWING) {
                $profile->update([
                    'image_drawing' => json_encode($pathimage),
                ]);
            }
        }
    }

    /**
     * @param \Illuminate\Http\UploadedFile[] $files
     *
     * @return array
     */
    public function videoUpload($files)
    {
        $video = $this->fileUpload->uploadVideo($files);
        $video = is_array($video) ? $video : [];

        return array_map(function ($vd) {
            return url($vd);
        }, $video);
    }

    /**
     * @param \Illuminate\Http\UploadedFile[] $images
     *
     */
    public function uploadVideoFile($id, $images)
    {
        $path = $this->fileUpload->moveTempUpload(Directory::RECRUIMENT_VIDEO, $images);

        $recruitment = Recruitment::findOrFail($id);

        $recruitment->update([
            'videos' => $path,
        ]);

        return $recruitment;
    }

    public function uploadImageFile($id, $images)
    {
        if ($images) {
            $pathimage = [];

            foreach ($images as $key => $item) {
                $path = $this->fileUpload->moveTempUpload(Directory::RECRUIMENT_IMAGE, $item);
                if (empty($path)) {
                    continue;
                }
                array_push($pathimage,$path);
            }

            $recruitment = Recruitment::findOrFail($id);

            $recruitment->update([
                'images' => json_encode($pathimage),
            ]);

            return $recruitment;

        }
    }
}
