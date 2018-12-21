<?php

namespace App\Services\Averagestar;
use App\Entities\Models\CommentUser;

/**
 * Class AveragestarService
 *
 * @package App\Services\Averagestar
 */
class AveragestarService
{
    /**
     * Rating Star.
     *
     * @param  $id
     *
     * @return double
     */
    public static function rating($id)
    {
        $comment = CommentUser::where('user_receive', $id)->get();

        $result = 0;

        $count_comment = count($comment);

        if (count($comment) > 0) {
            $sum_rate = 0;

            foreach ($comment as $item) {
                $sum_rate += $item->rate;
            }
            $result = $sum_rate / count($comment);
        }
        $output = new \stdClass();

        $output->count_comment = $count_comment;

        $output->result = $result;

        return $output;
    }
}
