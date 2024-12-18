<?php

namespace App\Repositories;

use App\Models\User;

class AuthRepository
{
    private User $userModel;

    public function __construct(User $user){
        $this->userModel = $user;
    }

    public function create(array $data){

        $this->userModel->fill($data);
        $this->userModel->save();
        
        return $this->userModel;
    }
}