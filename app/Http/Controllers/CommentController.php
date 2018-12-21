<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Models\CommentUser;
use Illuminate\Support\Facades\Validator;
use App\Constants\ResponseStatusCode;

class CommentController extends Controller
{
    /**
     * Api Comments.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function comment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_send'    => 'required|integer',
            'contents'     => 'required',
            'rate'         => 'required|numeric',
            'user_receive' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return $this->responseJson([
                'code'     => ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'messages' => $validator->errors()->all(),
            ]);
        }
        $comment = CommentUser::create([
            'contents'       => $request->contents,
            'rate'           => $request->rate,
            'user_send'      => $request->user_send,
            'user_receive'   => $request->user_receive,
            'profile_id'     => $request->profile_id,
            'recruitment_id' => $request->recruitment_id,
        ]);
        return response()->json([
            'code'    => ResponseStatusCode::OK,
            'comment' => $comment,
        ]);
    }

    /**
     * Get comment.
     *
     * @param  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComment($id)
    {
        $comment = CommentUser::with('user','profile','recruitment')->where('user_receive', $id)->get();

        if (!$comment) {
            return response()->json([
                'code' => ResponseStatusCode::NOT_FOUND,
                'data' => [
                    'message' => 'Comment not found',
                ],
            ], ResponseStatusCode::OK);
        }

        return $this->responseJson([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages' => 'Get list comment successful',
                'comment'  => $comment,
            ],
        ]);
    }
}
