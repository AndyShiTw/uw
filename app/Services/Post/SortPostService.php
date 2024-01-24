<?php

namespace App\Services\Post;

use App\Repositories\Post\PostRepository;
use App\Repositories\User\UserRepository;
use App\Services\ServiceAbstract;
use Illuminate\Support\Facades\DB;

class SortPostService extends ServiceAbstract
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
            'post_id' => 'required|integer',
            'seq' => 'required|integer',
            'target_post_id' => 'required|integer',
            'target_seq' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return ['result' => false, 'message' => '參數錯誤'];
        }

        return ['result' => true];
    }

    // 主要邏輯
    protected function process()
    {
        $postId = $this->params['post_id'];
        $seq = $this->params['seq'];
        $targetPostId = $this->params['target_post_id'];
        $targetSeq = $this->params['target_seq'];
        $userId = $this->params['user_id'];

        // 檢查會員是否存在
        $checkUserId = $this->userRepository->getUserById($userId);
        if($checkUserId === null) {
            return ['result' => false, 'code' => 2002, 'message' => '會員不存在'];
        }

        // 檢查文章的是否為自己持有，且確認post_id和seq是否相符，避免惡意串改
        $checkPostOwner = $this->postRepository->getPostByUserIdAndPostId($userId,$postId);
        // 透過JWT取得的user_id，檢查post_id是否為本人
        if($checkPostOwner === null) {
            return ['result' => false, 'code' => 2003, 'message' => '無權限修改'];
        }
        if($seq != $checkPostOwner->seq) {
            return ['result' => false, 'code' => 2003, 'message' => '無權限修改'];
        }

        // 檢查目標文章的是否為自己持有，且確認post_id和seq是否相符，避免惡意串改
        $checkTargetPostOwner = $this->postRepository->getPostByUserIdAndPostId($userId,$targetPostId);
        if($checkTargetPostOwner === null) {
            return ['result' => false, 'code' => 2003, 'message' => '無權限修改'];
        }
        if($targetSeq != $checkTargetPostOwner->seq) {
            return ['result' => false, 'code' => 2003, 'message' => '無權限修改'];
        }

        // 進入交易，交換Seq
        try {
            DB::beginTransaction();
        
            $this->postRepository->changePostSeq($postId,$seq,$targetPostId,$targetSeq);
        
            // 提交事务
            DB::commit();
        
            return ['result' => true, 'data' => ['postId' => $postId , "seq" => $targetPostId , "targetPostId" => $targetPostId , "targetSeq" => $seq]];
        } catch (\Exception $e) {
            DB::rollBack();
        
            return ['result' => false, 'code' => 4001, 'message' => '請聯繫開發人員'];
        }
    }
}
