<?php

namespace App\Services\User;

use App\Repositories\User\UserRepository;
use App\Services\ServiceAbstract;

class CreateUserService extends ServiceAbstract
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    // 驗證參數
    public function verify($params)
    {
        $this->params = $params;

        $validator = $this->validator($this->params, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ['result' => false, 'message' => '參數錯誤'];
        }

        // if(trim($params['password']) == '') {
        //     return ['result' => false, 'message' => '密碼不可為空'];
        // }

        return ['result' => true];
    }

    // 主要邏輯
    protected function process()
    {
        $email = $this->params['email'];
        $password = $this->params['password'];
        // 建立會員時預設為啟用狀態
        $status = '1';

        // 檢查會員是否已存在
        $checkEmail = $this->userRepository->getUserByEmail($email);

        if($checkEmail !== null) {
            // 已有重複Email
            return ['result' => false, 'code' => 2001, 'message' => 'Email重複'];
        }

        // 建立使用者，並回傳user_id
        $user = $this->userRepository->createUser($email,$password,$status);

        return ['result' => true, 'data' => ['userId' => $user->user_id]];
    }
}
