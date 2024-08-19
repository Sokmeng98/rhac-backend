<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;
use App\Models\MB_Learner;
use App\Models\MB_Professional;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CommentResource;

class CommentController extends Controller
{
    public function createComment(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'user_name' => 'nullable',
                'content' => 'required|string',
                'post_id' => 'exists:posts,id|nullable',
                'mb_learner_id' => 'exists:m_b__learners,id|nullable',
                'mb_professional_id' => 'exists:m_b__professionals,id|nullable',
                'checked' => 'boolean'
            ]
        );
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        $checked = false;
        if ($request->checked) {
            $checked = $request->checked;
        }
        $comment = Comment::create([
            'user_name' => $request->user_name,
            'content' => $request->content,
            'post_id' => $request->post_id,
            'mb_learner_id' => $request->mb_learner_id,
            'mb_professional_id' => $request->mb_professional_id,
            'checked' => $checked
        ]);
        return response(['data' => $comment], 201);
    }

    public function deleteComment($id)
    {
        if (!Comment::find($id)) {
            return response(['message' => '404 Not Found'], 404);
        }
        Comment::find($id)->delete();
        return response(['message' => 'Comment has been deleted successefully.']);
    }

    public function updateComment(Request $request, $id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response(['message' => '404 Not Found'], 404);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'user_name' => 'nullable',
                'content' => 'sometimes|required|string',
                'post_id' => 'exists:posts,id|nullable',
                'mb_learner_id' => 'exists:m_b__learners,id|nullable',
                'mb_professional_id' => 'exists:m_b__professionals,id|nullable',
                'checked' => 'boolean'
            ]
        );
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        $data = $request->all();
        $comment->fill($data);
        $comment->save();
        return $comment;
    }

    public function getComment(Request $request)
    {
        $comments = new Comment;
        if ($request->has('type')) {
            $type = $request->input('type');
            $id = $request->input('id');
            switch ($type) {
                case 'post':
                    $model = Post::find($id);
                    $commenttype = 'post_id';
                    break;
                case 'mb_learner':
                case 'mbLearner':
                    $model = MB_Learner::find($id);
                    $commenttype = 'mb_learner_id';
                    break;
                case 'mb_professional':
                case 'mbProfessional':
                    $model = MB_Professional::find($id);
                    $commenttype = 'mb_professional_id';
                    break;
                default:
                    return response(['message' => 'Invalid type'], 400);
            }
            if (!$model) {
                return response(['message' => '404 Not Found'], 404);
            }
            $comments->where($commenttype, $id);
        }
        return CommentResource::collection($comments->orderBy('created_at', 'desc')->paginate($request['limit']))->response();
    }

    public function getCommentById($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response(['message' => '404 Not Found'], 404);
        }
        return $comment;
    }
}
