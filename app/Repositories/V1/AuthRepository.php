<?php

namespace App\Repositories\V1;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthRepository {

    public function login($credentials)
    {
        if (!Auth::attempt($credentials)) {
            throw new Exception('Invalid Credentials');
        } else {
            $user = User::where('email', $credentials['email'])->first();
            Auth::login($user);

            $data = $this->createUserToken($user);

            return $data;
        }
    }

    public function register(Request $request)
    {
        $user = User::create([
            'email' => $request->email,
            'name' => $request->name,
            'business_name' => $request->business_name ?? null,
            'password' => Hash::make($request->password),
            'status_id' => 1,
        ]);

        $user->assignRole('user');
        $user = User::where('email', $request->email)->first();
        Auth::login($user);
        $data = $this->createUserToken($user);

        return $data;
    }

    public function update_profile(Request $request)
    {
        $user = User::find(Auth::id());

        if ($user->email != $request->email && User::where('email', $request->email)->first()) {
            throw new Exception('Email has already been taken');
        }

        $user->update([
            'email' => $request->email,
            'name' => $request->name,
            'business_name' => $request->business_name ?? null,
        ]);

        return $user;
    }
    public function get_profile()
    {
        $user = Auth::user();

        return $user;
    }

    private function createUserToken($user)
    {
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->save();
        $data['access_token'] = $tokenResult->accessToken;
        $data['token_type'] = 'Bearer';
        $data['expires_at'] = Carbon::parse($tokenResult->token->expires_at)->toDateTimeString();
        $user->getPermissionsViaRoles();
        $data['user'] = $user;

        return $data;
    }

    public function change_password($request)
    {
        $user = User::find(Auth::id());
        if ((!Hash::check(request('old_password'), $user->password))) {
            throw new Exception('Old Password did not match.');
        } else if ((Hash::check(request('new_password'), $user->password))) {
            throw new Exception('Please enter a password other than old one.');
        } else {
            $user->password = Hash::make($request->new_password);
            $user->save();
        }
    }

    public function logout()
    {
        $user = Auth::user();
        if ($user) {
            if ($user->token()->revoke()) {
                return true;
            } else {
                throw new Exception('Failed to logout');;
            }
        }

        throw new Exception('Un-authenticated');
    }
}
