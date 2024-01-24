<?php

namespace App\Services\Post;

use App\Repositories\Post\PostRepository;
use App\Repositories\User\UserRepository;
use App\Services\ServiceAbstract;

class UpdatePostService extends ServiceAbstract
{
    private $postRepository;
    private $userRepository;

    public function __construct(PostRepository $postRepository,UserRepository $userRepository)
    {
        parent::__construct();
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
    }

    // 驗證參數
    public function verify($params)
    {
        $this->params = $params;

        $validator = $this->validator($this->params, [
            'user_id' => 'required|string',
            'title' => 'required|string',
            'content' => 'required|string',
            'image' => 'required|url',
            'post_id' => 'required|string',
        ]);

        if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $this->params['image']) == false) {
            return ['result' => false, 'message' => 'image url錯誤'];
        }

        if ($validator->fails()) {
            return ['result' => false, 'message' => '參數錯誤'];
        }

        return ['result' => true];
    }

    // 主要邏輯
    protected function process()
    {
        $userId = $this->params['user_id'];
        $title = $this->params['title'];
        $content = $this->params['content'];
        $imageUrl = $this->params['image'];
        $postId = $this->params['post_id'];

        // 檢查會員是否存在
        $checkUserId = $this->userRepository->getUserById($userId);
        if($checkUserId === null) {
            return ['result' => false, 'code' => 2002, 'message' => '會員不存在'];
        }
        $checkPostOwner = $this->postRepository->getPostByUserIdAndPostId($userId,$postId);
        // 透過JWT取得的user_id，檢查post_id是否為本人
        if($checkPostOwner === null) {
            return ['result' => false, 'code' => 2003, 'message' => '無權限修改'];
        }

        // 修改文章
        $this->postRepository->updatePost($postId,$title,$content,$imageUrl);

        return ['result' => true, 'data' => ['postId' => $postId]];
    }
}
