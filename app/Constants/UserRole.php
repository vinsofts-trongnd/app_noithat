<?php

namespace App\Constants;

/**
 * Class UserRole
 *
 * @package App\Constants
 */
class UserRole
{
    /**
     * @var int GUEST
     */
    const GUEST = 0;

    /**
     * @var int USER_ROLE
     */
    const USER_ROLE = [
        [
            "type" => 1,
            "name" => "Khách",
        ],
        [
            "type" => 2,
            "name" => "Kiến trúc sư",
        ],
        [
            "type" => 3,
            "name" => "Thợ",
        ],
        [
            "type" => 4,
            "name" => "Nhà xưởng",
        ],
        [
            "type" => 5,
            "name" => "Nhà bán sỉ",
        ],
        [
            "type" => 6,
            "name" => "Sinh viên",
        ],
    ];
}

