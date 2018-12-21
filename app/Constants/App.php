<?php

namespace App\Constants;

/**
 * Class App
 *
 * @package App\Constants
 */
class App
{
    /**
     * @var string
     */
    const MIME_TYPE_IMAGE = 'jpg,jpeg,png,gif,bmp,tif';

    /**
     * @var int
     */
    const IMAGE_MAXSIZE = 10240; // kb

    /**
     * @var string
     */
    const MIME_TYPE_VIDEO = 'mimetypes:videos/x-ms-asf,videos/x-flv,videos/mp4,application/x-mpegURL,videos/MP2T,videos/3gpp,videos/quicktime,videos/x-msvideo,videos/x-ms-wmv,videos/avi';

    /**
     * @var int
     */
    const VIDEO_MAXSIZE = 51200; // MB
}
