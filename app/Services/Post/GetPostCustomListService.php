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
        $perPage = 10;

        // 檢查Email是否存在
        $checkEmail = $this->userRepository->getUserByEmail($email);
        if($checkEmail === null) {
            return ['result' => false, 'code' => 2001, 'message' => '查無帳號'];
        }

        $userId = $checkEmail->user_id;

        // 取得文章
        $postList = $this->postRepository->getPostListByUserId($userId,PHP_INT_MAX,'1','post_id','DESC','','');
        
        $dataList = $postList->filter(function ($post) {
            return $post->post_id % 2 == 1;
        });

        // 獲取總數據量
        $totalData = $dataList->count();
        $totalPage = ceil($totalData/$perPage);
        if($page > $totalPage) {
            $page = $totalPage;
        }

        // 計算當前頁面的數據
        $offset = ($page - 1) * $perPage;
        $itemsForCurrentPage = array_slice($dataList->all(), $offset, $perPage);

        // 建立分頁器
        $paginator = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $totalData,
            $perPage,
            $page,
        );
        
        $dataList = [];
        foreach($paginator as $post) {
            array_push($dataList,[
                'post_id' => $post->post_id,
                'title' => $post->title,
                'content' => $post->content,
                'image' => $post->image,
                'updated_at' => $post->updated_at->format('Y-m-d H:i:s')
            ]);
            // dd($post->updated_at);
        }

        return ['result' => true, 'data' => ["dataList" => $dataList , "totalPage" => $totalPage]];
    }
}
