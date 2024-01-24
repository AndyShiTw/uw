<?php

namespace App\Services\Post;

use App\Repositories\Post\PostRepository;
use App\Repositories\User\UserRepository;
use App\Services\ServiceAbstract;

class InactivePostService extends ServiceAbstract
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
            'post_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ['result' => false, 'message' => '參數錯誤'];
        }

        return ['result' => true];
    }

    // 主要邏輯
    protected function process()
    {
        $userId = $this->params['user_id'];
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

        // 如果是15就不異動
        $postStatus = $checkPostOwner->post_status;

        if($postStatus == '10') {
            // 隱藏文章
            $this->postRepository->updatePostStatus($postId,'15');
        } 

        return ['result' => true, 'data' => ['postId' => $postId]];
    }
}
