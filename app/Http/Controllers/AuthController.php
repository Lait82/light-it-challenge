<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignInRequest;
use App\Interfaces\AuthRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AuthController extends Controller
{
    private AuthRepositoryInterface $authRepo;
    public function __construct(AuthRepositoryInterface $authRepo) {
        $this->authRepo = $authRepo;
    }

    public function signIn(SignInRequest $request){
        try {
            $validated = $request->validated();
            return Response::json('too piola', 204);
        } catch (Exception $e) {
            return Response::json('Internal Error', 500);
        }
    }
}
