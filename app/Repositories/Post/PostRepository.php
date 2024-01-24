<?php

namespace App\Repositories\Post;

use App\Models\Post;
use Illuminate\Support\Facades\DB;

class PostRepository
{
    private $post;

    public function __construct(
        Post $post
    ) {
        $this->post = $post;
    }

    public function createPost($userId,$title,$content,$image,$postStatus,$maxSeq)
    {
        return $this->post->create([
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
            'image' => $image,
            'post_status' => $postStatus,
            'seq' => $maxSeq,
        ]);
    }

    public function getPostListByUserId($userId,$perPage,$page,$sortBy,$sortOrder,$searchBy,$searchContent) {
        $data = $this->post
                ->where("post_status",10)
                ->where("user_id",$userId)
                ->orderBy($sortBy, $sortOrder);

        if($searchBy != '') {
            $data = $data->where($searchBy,'like','%'.$searchContent.'%');
        }
        

        $data = $data->paginate($perPage, ['*'], 'page', $page);
        return $data;
    }

    public function getPostByUserIdAndPostId($userId,$postId) {
        return $this->post
            ->where("user_id",$userId)
            ->where("post_id",$postId)
            ->whereIn('post_status',["10","15"])
            ->first();
    }

    public function updatePost($postId,$title,$content,$image)
    {
        $query = $this->post
            ->where('post_id', $postId)
            ->whereIn('post_status',['10','15'])
            ->update([
                'title' => $title,
                'content' => $content,
                'image' => $image,
            ]);
        return $query;
    }

    public function softDeletePost($postId,$title,$content,$image)
    {
        $query = $this->post
            ->where('post_id', $postId)
            ->whereIn('post_status',['10','15'])
            ->update([
                'post_status' => '20',
            ]);
        return $query;
        
    }

    public function getAllPostListByUserId($userId,$perPage,$page,$sortBy,$sortOrder,$searchBy,$searchContent) {
        $data = $this->post
                ->whereIn("post_status",[10,15])
                ->where("user_id",$userId)
                ->orderBy($sortBy, $sortOrder);

        if($searchBy != '') {
            $data = $data->where($searchBy,'like','%'.$searchContent.'%');
        }
        

        $data = $data->paginate($perPage, ['*'], 'page', $page);
        return $data;
    }

    public function updatePostStatus($postId,$postStatus)
    {
        $query = $this->post
            ->where('post_id', $postId)
            ->whereIn('post_status',['10','15'])
            ->update([
                'post_status' => $postStatus,
            ]);
        return $query;
    }

    public function getPostMaxSeq($userId) {
        return $this->post
            ->where("user_id",$userId)
            ->max('seq');
    }
}
