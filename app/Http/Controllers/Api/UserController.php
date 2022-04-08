<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\GetUserRequest;
use App\Models\User;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserResourceCollection;
use CoreProc\WalletPlus\Models\WalletType;
use CoreProc\WalletPlus\Models\Wallet;
use CoreProc\WalletPlus\Models\WalletLedger;
use App\Imports\StatesImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\State;

class UserController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api', ['except'=>['login','register']]);
    }

    //Register
    public function register(CreateUserRequest $request){
        $validated_data = $request->validated();

        $user = User::create([
            'name'     => $validated_data['name'],
            'email'        => $validated_data['email'],
            'password'     => bcrypt($validated_data['password'])
        ]);

        if($user){
            return response()->json(
                [
                    'status' => 'success',
                    'data' => $user
                ],201);
        }else{
            return response()->json([
                'message' => 'Registration Failed, Please try again!',
                'status' => 'failed'
            ], 400);
        }
    }

    //Login
    public function login(LoginRequest $request){
        $validated_data = $request->validated();

        $token_validity = 24 * 60;
        $this->guard()->factory()->setTTL($token_validity);
        if(!$token = $this->guard()->attempt($validated_data)){
            return response()->json(['error'=>'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    //respondWithToken function used in login
    protected function respondWithToken($token){
        return response()->json([
            'token'=>$token,
            'token_type'=>'Bearer',
            'token_validity'=>$this->guard()->factory()->getTTL()*60
        ]);
    }

    //guard function used in login and logout
    protected function guard(){
        return Auth::guard();
    }

    //logout
    public function logout(){
        $this->guard()->logout();

        return response()->json(['message'=>'User successfully logged out']);
    }

   //Refresh Token
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    //Get User profile
    public function profile()
    {
        return response()->json(auth()->user());
    }

    //Retrieving Users
    public function getAllUsers(){
        return new UserResourceCollection(User::all());
    }

    //Get User details
    public function getUserDetails(GetUserRequest $request){
        $validated_data = $request->validated();
        $user = User::whereId($validated_data['user_id'])->first();
        return new UserResource($user);
    }

    //All details - Users/Wallets/Transactions
    public function appDetails(){
        $userCount          = User::all()->count();
        $walletCount        = Wallet::all()->count();
        $totalWalletBalance = Wallet::all()->sum('raw_balance');

        return response()->json(
            [
                'status'               => 'success',
                'user_count'           => $userCount,
                'wallet_count'         => $walletCount,
                'total_wallet_balance' => $totalWalletBalance,
            ],200);

    }

    //Import Excel file
    public function importExcelFile(){
        $import = Excel::import(new StatesImport,request()->file('file'));

        if($import){
            return response()->json(
                [
                    'status'   => 'success',
                    'message'  => 'Imported Successfully!'
                ],201);
        }else{
            return response()->json([
                'message' => 'Something went wrong, Please try again!',
                'status' => 'failed'
            ], 400);
        }
    }

    //view file content
    public function viewFile(){
        $states = State::all();
        
        return response()->json([
            'status' => 'success',
            'data'   => $states
        ], 200);
    }
}
