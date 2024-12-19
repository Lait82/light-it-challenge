<?php

namespace App\Http\Controllers;

use App\Http\Requests\LogInRequest;
use App\Http\Requests\SignUpRequest;
use App\Interfaces\AuthRepositoryInterface;
use App\Mail\ConfirmEmail;
use App\Models\User;
use Exception;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    private AuthRepositoryInterface $authRepo;
    public function __construct(AuthRepositoryInterface $authRepo) {
        $this->authRepo = $authRepo;
    }

    public function signUp(SignUpRequest $request){
        try {
            $validated = $request->validated();

            // Guardo la imagen del id primero por si en caso de que falle, no cree un usuario sin ID.
            $idName = str()->random(5);
            $idExt = $validated['id_photo']->extension();
            $idFullname = "$idName.$idExt";
            $validated['id_photo']->storeAs("/ids", $idFullname);
            $validated['id_photo'] = $idFullname;
            
            // Creo al usuario.
            $validated['password'] = Hash::make($validated['password']);
            $user = $this->authRepo->create($validated);

            // Creo su token.
            $authToken = $user->createToken('auth-token', ['*'], now()->addDay());

            //envio el mail
            Mail::to($user->email)
            ->queue(new ConfirmEmail);

            return Response::json(['token' => $authToken->plainTextToken], 204);
        } catch (Exception $e) {
            dd($e);
            return Response::json('Internal Error', 500);
        }
    }

    public function logIn(LogInRequest $request){
        try {
            $validated = $request->validated();

            $successfulLogin = Auth::attempt($validated);

            if (!$successfulLogin) {
                return Response::json(NULL, 401);
            }

            $user = Auth::user();

                //Revoke all the otheer tokens
                $user->tokens()->delete();
                $authToken = $user->createToken('auth-token', ['*'], now()->addDay());
                return Response::json(['user_data' => $user, 'token' => $authToken->plainTextToken], 200);
        } catch (Exception $e) {
            dd($e);
            return Response::json('Internal Error', 500);
        }
    }
    public function patientHome(){
        try {
            $user = Auth::user();
            return Response::json(['status' => 'employed', 'hopefully' => 'please :\')', 'user' => $user], 200);        
        } catch (Exception $e) {
            dd($e);
            return Response::json('Internal Error', 500);
        }
    }
}
