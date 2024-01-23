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

    public function createPost($userId,$title,$content,$image,$postStatus)
    {
        return $this->post->create([
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
            'image' => $image,
            'post_status' => $postStatus,
        ]);
    }

    public function getPostListByUserId($userId,$perPage,$page,$sortBy,$sortOrder,$searchBy,$searchContent) {
        $data = $this->post
                ->where("post_status",10)
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
            ->where('post_status',"10")
            ->first();
    }

    public function updatePost($postId,$title,$content,$image)
    {
        $query = $this->post
            ->where('post_id', $postId)
            ->where('post_status',"10")
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
            ->where('post_status','10')
            ->update([
                'post_status' => '20',
            ]);
        return $query;
        
    }
}
