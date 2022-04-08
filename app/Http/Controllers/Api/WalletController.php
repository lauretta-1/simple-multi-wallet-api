<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Wallet\CreateWalletRequest;
use App\Http\Requests\Wallet\GetWalletRequest;
use App\Http\Requests\Wallet\CreateUserWalletRequest;
use App\Http\Requests\Wallet\SendMoneyRequest;
use App\Http\Resources\Wallet\WalletResource;
use App\Http\Resources\Wallet\WalletResourceCollection;
use CoreProc\WalletPlus\Models\WalletType;
use CoreProc\WalletPlus\Models\Wallet;
use CoreProc\WalletPlus\Models\WalletLedger;

class WalletController extends Controller
{
    //Create Wallet types
    public function createWallet(CreateWalletRequest $request){
        $validated_data = $request->validated();

        $walletType = WalletType::create([
            'name' => $validated_data['name'],
            'decimals' => $validated_data['decimals'], //Set how many decimal points your wallet accepts here.
            'minimum_balance' => $validated_data['minimum_balance'],
            'monthly_interest_rate' => $validated_data['monthly_interest_rate'],
        ]);

        if($walletType){
            return response()->json(
                [
                    'status' => 'success',
                    'data' => $walletType
                ],201);
        }else{
            return response()->json([
                'message' => 'Something went wrong, Please try again!',
                'status' => 'failed'
            ], 400);
        }
    }

    //Create User wallet
    public function createUserWallet(CreateUserWalletRequest $request){
        $validated_data = $request->validated();
        $walletType = WalletType::whereId($validated_data['wallet_type_id'])->first();
        $user = User::whereId(auth()->user()->id)->first();
        
        $wallet = $user->wallets()->create([
            'wallet_type_id' => $walletType->id,
            'raw_balance'    => $walletType->minimum_balance
        ]);

        if($user->wallets()){
            return response()->json(
                [
                    'status' => 'success',
                    'data' => $wallet
                ],201);
        }else{
            return response()->json([
                'message' => 'Something went wrong, Please try again!',
                'status' => 'failed'
            ], 400);
        }
    }

    //Retrieve all wallets types in the system
    public function getAllWallets(){
        $wallets = WalletType::latest()->get();

        return response()->json(
            [
                'status' => 'success',
                'data' => $wallets
            ],200);
    }

    //Wallet Details
    public function getWalletDetails(GetWalletRequest $request){
        $validated_data = $request->validated();  
        $wallet = Wallet::whereId($validated_data['wallet_id'])->first();
        
        return new WalletResource($wallet);
    }

    //Send Money from one wallet to the other
    public function sendMoney(SendMoneyRequest $request){
        $validated_data = $request->validated();
        
        $senderWallet = Wallet::whereId($validated_data['sender_wallet_id'])->first();
        $receiverWallet = Wallet::whereId($validated_data['receiver_wallet_id'])->first();

        if($senderWallet && $receiverWallet){
            $amount = (int)$validated_data['amount'];
            $senderWallet->decrementBalance($amount);
            $receiverWallet->incrementBalance($amount);

            return response()->json([
                'status' => "success",
                'sender_wallet_balance' => $senderWallet->balance,
                'receiver_wallet_balance' => $receiverWallet->balance,
            ], 400);            
        }else{
            return response()->json([
                'status' => "failed",
                'message' => "Sender or receiver's wallet does not exist!",
            ], 400);
        }

    }

}
