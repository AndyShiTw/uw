<?php

namespace App\Services\Post;

use App\Repositories\Post\PostRepository;
use App\Repositories\User\UserRepository;
use App\Services\ServiceAbstract;
use Illuminate\Support\Facades\DB;

class CreatePostService extends ServiceAbstract
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
            'image' => 'required|url'
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
        $postStatus = '10';

        
        // 檢查會員是否存在
        $checkUserId = $this->userRepository->getUserById($userId);
        if($checkUserId === null) {
            return ['result' => false, 'code' => 2002, 'message' => '會員不存在'];
        }

        try {
            DB::beginTransaction();
        
            $maxSeq = $this->postRepository->getPostMaxSeq($userId) ?? 0;
            $maxSeq++;
        
            $post = $this->postRepository->createPost($userId, $title, $content, $imageUrl, $postStatus, $maxSeq);
        
            // 提交事务
            DB::commit();
        
            return ['result' => true, 'data' => ['postId' => $post->post_id]];
        } catch (\Exception $e) {
            DB::rollBack();
        
            return ['result' => false, 'code' => 4001, 'message' => '請聯繫開發人員'];
        }
    }
}
