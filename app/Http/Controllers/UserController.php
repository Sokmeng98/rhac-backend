<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Resources\UserResource;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class UserController extends Controller
{
    use HasApiTokens, HasRoles;
    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required',
                'password' => 'required'
            ],
        );
        if ($validator->fails()) {
            return response([
                'message' => $validator->errors()
            ], 400);
        }
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('Laravel Password Grant Client')->accessToken;
            $response = ['access_token' => $token];
            return response($response, 200);
        }
        return response()->json(['message' => 'Invalid Credentials'], 401);
    }

    public function registerUser(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8',
                'role' => 'required'
            ],
        );
        if ($validator->fails()) {
            return response([
                'message' => $validator->errors()
            ], 400);
        }
        if (count(User::where('email', $request->email)->get()) != 0) {
            return response([
                'message' => 'The email has already been taken.'
            ], 409);
        }
        if ($request->role === 'admin' || $request->role === 'user') {
            $Role = Role::where('name', $request->role)->get();
            User::create([
                'name' => $request['name'],
                'password' => Hash::make($request['password']),
                'email' => $request['email'],
            ])->assignRole($Role);
        } else {
            return response([
                'message' => "Invalid role"
            ], 400);
        }
        //event(new Registered($user));
        return response([
            'message' => "You have successfully registered"
        ], 201);
    }

    public function logout(Request $request)
    {
        $user = Auth::user()->token();
        $user->revoke();
        return response()->json([
            'message' => "You have logout successfully"
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email'
            ]
        );
        if ($validator->fails()) {
            return response([
                'message' => $validator->errors()
            ], 400);
        }
        if (User::where('email', $request->email)->get()->count() == 0) {
            return response([
                'message' => 'We can\'t find a user with that email address.'
            ], 400);
        }
        $status = Password::sendResetLink(
            $request->only('email')
        );
        if ($status == Password::RESET_LINK_SENT) {
            return [
                'message' => __($status)
            ];
        } elseif (__($status) === 'Please wait before retrying.') {
            return response([
                'message' => 'We\'ve already sent you the reset link.'
            ], 400);
        }

        return response([
            'message' => __($status)
        ], 400);
    }

    public function reset(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'token' => 'required',
                'email' => 'required|email',
                'password' => ['required', 'confirmed', RulesPassword::defaults()],
                'password_confirmation' => 'required'
            ],
            [
                'password.confirmed' => 'The password and password confirmation does not matched.'
            ]
        );
        if ($validator->fails()) {
            return response([
                'message' => $validator->errors()
            ], 400);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response([
                'message' => 'Your password has been reset successfully.'
            ]);
        }

        return response([
            'message' => __($status)
        ], 400);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'password' => 'required|min:8',
                'new_password' => 'required|min:8'
            ]
        );
        if ($validator->fails()) {
            return response([
                'message' => $validator->errors()
            ], 400);
        }
        $currentPassword = Auth::user()->password;
        if (Hash::check($request['password'], $currentPassword) == false) {
            return response(['message' => 'Your old password is incorrect'], 400);
        }
        if (Hash::check($request['new_password'], $currentPassword) == true) {
            return response(['message' => 'New password and old password should not be the same.'], 400);
        }
        $user = Auth::user();
        $user->password = Hash::make($request['new_password']);
        $user->save();
        $response = ['message' => 'Your password has been updated successfully.'];
        return response($response, 200);
    }

    public function showUserInfo()
    {
        $user_id = Auth::user()->id;
        $response = User::where('id', $user_id)->with('roles')->get();
        return UserResource::collection($response)->response();
    }

    public function updateUser(Request $request, $id)
    {
        $data = $request->all();
        $user = User::where('id', $id)->firstOrFail();
        $exist = User::where('email', $request->email)->get();
        if (count($exist) && $user->email != $data['email']) {
            return response()->json([
                'code' => 505,
                'message' => "That email is already taken"
            ]);
        }
        $Role = Role::where('name', $request->role)->get();
        $user->fill($data)->syncRoles($Role);
        $user->save();
        return $user;
    }

    public function sendVerificationEmail(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required'
            ]
        );
        if ($validator->fails()) {
            return response([
                'message' => $validator->errors()
            ], 400);
        }
        $user_email = Auth::user()->email;
        if ($user_email !== $request->email) {
            return response([
                'message' => 'We can\'t find a user with that email address'
            ], 400);
        } elseif ($request->user()->hasVerifiedEmail()) {
            return [
                'message' => 'Already Verified'
            ];
        }

        $request->user()->sendEmailVerificationNotification();

        return ['status' => 'We have emailed you verification-link!'];
    }

    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return [
                'message' => 'Email already verified'
            ];
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return [
            'message' => 'Email has been verified'
        ];
    }
}
