<?php

namespace App\Services\Post;

use App\Repositories\Post\PostRepository;
use App\Repositories\User\UserRepository;
use App\Services\ServiceAbstract;
use Illuminate\Pagination\LengthAwarePaginator;

class GetPostCustomListService extends ServiceAbstract
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
            'email' => 'required|email',
            'page' => 'integer',
        ]);

        if ($validator->fails()) {
            return ['result' => false, 'message' => '參數錯誤'];
        }

        return ['result' => true];
    }

    // 主要邏輯
    protected function process()
    {
        $email = $this->params['email'];
        $page = $this->params['page'] ?? 1;
        $perPage = PHP_INT_MAX;

        // 檢查Email是否存在
        $checkEmail = $this->userRepository->getUserByEmail($email);
        if($checkEmail === null) {
            return ['result' => false, 'code' => 2001, 'message' => '查無帳號'];
        }

        $userId = $checkEmail->user_id;

        // 發布文章
        $postList = $this->postRepository->getPostListByUserId($userId,$perPage,$page,'post_id','DESC','','');
        

        $dataList = $postList->filter(function ($post) {
            return $post->post_id % 2 == 1;
        });

        $paginator = new LengthAwarePaginator(
            $dataList,
            $dataList->count(),
            $perPage,
            $page,
        );

        $totalPage = $dataList->count();
        
        $dataList = [];
        foreach($paginator as $post) {
            array_push($dataList,[
                'post_id' => $post->post_id,
                'title' => $post->title,
                'content' => $post->content,
                'image' => $post->image,
                'updated_at' => $post->updated_at,
            ]);
        }

        return ['result' => true, 'data' => ["dataList" => $dataList , "totalPage" => $totalPage]];
    }
}
