<?php

namespace App\Services\Post;

use App\Repositories\Post\PostRepository;
use App\Repositories\User\UserRepository;
use App\Services\ServiceAbstract;

class GetPostListService extends ServiceAbstract
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
        $sortBy = $this->params['sortBy'] ?? 'post_id';
        $sortOrder = $this->params['sortOrder'] ?? 'DESC';
        $searchBy = $this->params['searchBy'] ?? '';
        $searchContent = $this->params['searchContent'] ?? '';
        $perPage = 10;

        if(in_array($sortBy,['post_id','title','content','updated_at']) === false) {
            $sortBy = 'post_id';
        }

        if(in_array($sortOrder,['DESC','ASC']) === false) {
            $sortOrder = 'DESC';
        }

        if(in_array($searchBy,['post_id','title','content']) === false) {
            $searchBy = '';
        }

        // 檢查Email是否存在
        $checkEmail = $this->userRepository->getUserByEmail($email);
        if($checkEmail === null) {
            return ['result' => false, 'code' => 2001, 'message' => '查無帳號'];
        }

        $userId = $checkEmail->user_id;

        // 發布文章
        $postList = $this->postRepository->getPostListByUserId($userId,$perPage,$page,$sortBy,$sortOrder,$searchBy,$searchContent);
        $lastPage = $postList->lastPage();
        $totalPage = $postList->total();

        if($page > $lastPage) {
            $postList = $this->postRepository->getPostListByUserId($userId,$perPage,$lastPage,$sortBy,$sortOrder,$searchBy,$searchContent);
        }

        $dataList = [];
        foreach($postList as $post) {
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
