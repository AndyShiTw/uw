<?php

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    private $users;

    public function __construct(
        User $users
    ) {
        $this->users = $users;
    }

    public function getUserByEmail($email)
    {
        return $this->users->select(
            'user_id',
        )->where('email', $email)->first();
    }

    public function getUserById($userId)
    {
        return $this->users->select(
            'user_id',
        )->where('user_id', $userId)->first();
    }

    public function createUser($email,$password,$status)
    {
        return $this->users->create([
            'email' => $email,
            'password' => Hash::make($password),
            'status' => $status,
        ]);
    }

    public function getUserData($email,$password) 
    {
        return $this->users->select(
            'user_id',
        )->where('email', $email)
        ->where('password',$password)
        ->where('status','1')
        ->first();
    }
}
