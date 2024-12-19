<?php

namespace App\Repositories;

use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;

class AuthRepository implements AuthRepositoryInterface
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
    
    public function update(array $data, $id){
        return $this->userModel->where('id', $id)->update($data);
    }
    public function delete($id){
        return $this->userModel->destroy($id);
    }
    public function find($id){
        return $this->userModel->find($id);
    }

}