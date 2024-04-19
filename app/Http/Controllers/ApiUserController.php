<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;

class ApiUserController extends Controller
{
    /**
     * @return array|\Illuminate\Http\JsonResponse|object
     */
    public function get()
    {
        return $this->sendResponseSuccess(User::all()->toArray());
    }

    /**
     * @param Request $request
     * @return void
     */
    public function save(Request $request)
    {
        $attribute = $request->validate(
            [
                'email' => 'required|unique:users,email',
                'name' => 'required',
                'user_code' => 'required|unique:users,user_code',
                'level' => 'required',
                'note' => '',
            ],
            [
                'email.required' => 'Email không được để trống',
                'email.unique' => 'Email dã tồn tại',
                'name.required' => 'Họ và tên không được để trống',
                'user_code.unique' => 'Mã tài khoản đã tồn tại',
                'level.required' => 'Loại tài khoản không được để trống'
            ]
        );

        $attribute['password'] = bcrypt('123456');
        User::insert($attribute);
    }

    public function update(Request $request, $id)
    {
        $attribute = $request->validate(
            [
                'email' => "required|unique:users,email,{$id}",
                'name' => 'required',
                'user_code' => "required|unique:users,user_code,{$request->user_code},user_code",
                'level' => 'required',
                'note' => '',
            ],
            [
                'email.required' => 'Email không được để trống',
                'email.unique' => 'Email dã tồn tại',
                'name.required' => 'Họ và tên không được để trống',
                'user_code.unique' => 'Mã tài khoản đã tồn tại',
                'level.required' => 'Loại tài khoản không được để trống'
            ]
        );

        User::find($request->id)->update($attribute);
    }

    public function delete($id)
    {
        User::where('id', $id)->delete();
    }

    public function login(Request $request)
    {
        $attributes = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ],
            [
                'email.required' => 'Email không được để trống',
                'email.email' => 'Email không đúng định dạng',
                'password.required' => 'Mật khẩu không được để trống',
            ]);
        $user = User::where('email', $request->email)->first();
        if (!$user || !\Hash::check($request->password, $user->password)) {
            return $this->sendResponse(Response::HTTP_UNPROCESSABLE_ENTITY, ['errors' => 'Tài khoản hoặc mật khẩu không đúng!']);
        }

        if (\Auth::attempt($attributes)) {
            $accessToken = $user->createToken('authToken')->plainTextToken;
            return $this->sendResponseSuccess([
                'token' => $accessToken,
                'user' => auth()->user(),
            ]);
        }

        return $this->sendResponseError();
    }
}
