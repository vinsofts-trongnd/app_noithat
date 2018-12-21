<?php

function notification($message, $users, $additionData){
    OneSignal::sendNotificationUsingTags(
        $message,
        array(
            ["field" => "tag", "key" => "user_id", "relation" => "=", "value" => $users],
        ),
        $url = null,
        $data = $additionData,
        $buttons = null,
        $schedule = null
    );
}

/**
 * Distance calculation coordinate
 */
function distanceCalculation($latLngRecruitment, $latLngProfile)
{
    $latitudeRecruitment  = strtok($latLngRecruitment, ',' );
    $longitudeRecruitment = strtok('' );
    $addressDesired       = $latLngProfile;
    $latitudeProfile      = strtok($addressDesired, ',');
    $longitudeProfile     = strtok(',');

    $theta = $longitudeRecruitment - $longitudeProfile;
    $dist  = sin(deg2rad($latitudeRecruitment)) * sin(deg2rad($latitudeProfile)) + cos(deg2rad($latitudeRecruitment)) * cos(deg2rad($latitudeProfile)) * cos(deg2rad($theta));
    $dist  = acos($dist);
    $dist  = rad2deg($dist);
    $miles = $dist * 60 * 1.1515 * 1.609344;
    return $miles;
}
