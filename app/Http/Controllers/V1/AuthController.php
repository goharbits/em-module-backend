<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Repositories\V1\AuthRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $repository;

    public function __construct()
    {
        $this->repository = new AuthRepository;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            $credentials = $request->only('email', 'password');
            $data = $this->repository->login($credentials);

            DB::commit();
            return sendSuccess('Login Successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed',
            'name' => 'required|string',
            'business_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            $data = $this->repository->register($request);

            DB::commit();
            return sendSuccess('Register Successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }

    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'name' => 'required|string',
            'business_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            $data = $this->repository->update_profile($request);

            DB::commit();
            return sendSuccess('Update Successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function profile()
    {
        try {
            DB::beginTransaction();
            $data = $this->repository->get_profile();

            DB::commit();
            return sendSuccess('Profile fetched Successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }

    public function change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required|same:new_password',
        ]);
        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }
        try {
            DB::beginTransaction();
            $this->repository->change_password($request);
            DB::commit();
            return sendSuccess('Password updated successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }

    public function logout()
    {
        try {
            DB::beginTransaction();
            $this->repository->logout();

            DB::commit();
            return sendSuccess('Logout successfully!', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
}
