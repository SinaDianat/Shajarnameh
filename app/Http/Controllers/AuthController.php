<?php

namespace App\Http\Controllers;

use App\Models\EmailVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\PasswordReset;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:users,name',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8',
            ]);

            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            EmailVerification::updateOrCreate(
                ['email' => $request->email],
                [
                    'code' => $code,
                    'user_data' => json_encode([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                    ]),
                    'expires_at' => Carbon::now()->addMinutes(30),
                    'last_sent_at' => Carbon::now(),
                ]
            );

            Mail::send('emails.email_verification', ['code' => $code], function ($message) use ($request) {
                $message->to($request->email)->subject('کد تأیید ایمیل');
            });

            return redirect()->route('verify-email')->with('email', $request->email)->with('success', 'کد تأیید به ایمیل شما ارسال شد. لطفاً کد را وارد کنید.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', 'خطا در اعتبارسنجی');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطای سرور: ' . $e->getMessage());
        }
    }

    public function verifyEmail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'code' => 'required|string|size:6',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors())->withInput()->with('error', 'خطا در اعتبارسنجی');
            }

            $verification = EmailVerification::where('email', $request->email)
                                            ->where('code', $request->code)
                                            ->first();

            if (!$verification) {
                return redirect()->back()->with('error', 'کد تأیید یا ایمیل نامعتبر است')->withInput();
            }

            if (Carbon::now()->gt($verification->expires_at)) {
                $verification->delete();
                return redirect()->back()->with('error', 'کد تأیید منقضی شده است. لطفاً دوباره ثبت‌نام کنید.')->withInput();
            }

            $userData = json_decode($verification->user_data, true);    
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
            ]);

            $verification->delete();

            $token = JWTAuth::fromUser($user);
            $cookie = cookie('jwt', $token, config('jwt.ttl'), '/', null, false, true, false, 'Lax');


            $redirectRoute = $user->is_admin ? 'admin.users' : 'user.panel';
            return redirect()->route($redirectRoute)->with('success', 'ایمیل تأیید شد. خوش آمدید!')->withCookie($cookie);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطای سرور: ' . $e->getMessage())->withInput();
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors())->withInput()->with('error', 'خطا در اعتبارسنجی');
            }

            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return redirect()->back()->with('error', 'ایمیل یا رمز عبور اشتباه است')->withInput();
            }

            $user = Auth::user();
            $cookie = cookie('jwt', $token, config('jwt.ttl'), '/', null, false, true, false, 'Lax');



            $redirectRoute = $user->is_admin ? 'admin.panel' : 'user.panel';
            return redirect()->route($redirectRoute)->with('success', 'ورود با موفقیت انجام شد')->withCookie($cookie);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return redirect()->back()->with('error', 'خطا در تولید توکن: ' . $e->getMessage())->withInput();
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'خطای سرور: ' . $e->getMessage())->withInput();
        }
    }

    public function logout(Request $request)
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {

                return redirect()->route('login')->with('error', 'توکن یافت نشد');
            }

            $payload = JWTAuth::getPayload($token);
            $expiresAt = \Carbon\Carbon::createFromTimestamp($payload->get('exp'));
            $tokenHash = hash('sha256', $token);

            DB::table('token_blacklist')->insert([
                'token' => $token,
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            JWTAuth::invalidate($token);

            $cookie = cookie()->forget('jwt');

            return redirect()->route('login')->with('success', 'خروج با موفقیت انجام شد')->withCookie($cookie);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return redirect()->route('login')->with('error', 'توکن نامعتبر است');
        } catch (\Exception $e) {

            return redirect()->route('login')->with('error', 'خطای سرور: ' . $e->getMessage());
        }
    }

    public function resendVerificationCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors())->withInput()->with('error', 'خطا در اعتبارسنجی');
            }

            $verification = EmailVerification::where('email', $request->email)->first();

            if (!$verification) {
                return redirect()->back()->with('error', 'رکورد تأیید برای این ایمیل یافت نشد. لطفاً دوباره ثبت‌نام کنید.')->withInput();
            }

            if (Carbon::now()->gt($verification->expires_at)) {
                $verification->delete();
                return redirect()->back()->with('error', 'کد تأیید منقضی شده است. لطفاً دوباره ثبت‌نام کنید.')->withInput();
            }

            if ($verification->last_sent_at && Carbon::now()->lt($verification->last_sent_at->addMinutes(1))) {
                $secondsRemaining = $verification->last_sent_at->addMinutes(1)->diffInSeconds(Carbon::now());
                return redirect()->back()->with('error', "لطفاً {$secondsRemaining} ثانیه دیگر صبر کنید تا بتوانید کد جدید درخواست کنید.")->withInput();
            }

            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            $verification->update([
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(30),
                'last_sent_at' => Carbon::now(),
            ]);

            Mail::send('emails.email_verification', ['code' => $code], function ($message) use ($request) {
                $message->to($request->email)->subject('کد تأیید ایمیل');
            });

            return redirect()->route('verify-email')->with('success', 'کد تأیید جدید به ایمیل شما ارسال شد.')->with('email', $request->email);
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'خطای سرور: ' . $e->getMessage())->withInput();
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255|exists:users,email',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors())->withInput()->with('error', 'خطا در اعتبارسنجی');
            }

            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            PasswordReset::updateOrCreate(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'code' => $code,
                    'expires_at' => Carbon::now()->addMinutes(30),
                ]
            );

            Mail::send('emails.password_reset', ['code' => $code], function ($message) use ($request) {
                $message->to($request->email)->subject('کد بازنشانی رمز عبور');
            });

            return redirect()->route('verify-reset-code')->with('success', 'کد بازنشانی به ایمیل شما ارسال شد. لطفاً کد را وارد کنید.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطای سرور: ' . $e->getMessage())->withInput();
            return redirect()->back()->with('error', 'خطای سرور: ' . $e->getMessage())->withInput();
            return redirect()->back()->with('error', 'خطای سرور: ' . $e->getMessage())->withInput();
        }
    }

    public function verifyResetCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'code' => 'required|string|size:6',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors())->withInput()->with('error', 'خطا در اعتبارسنجی');
            }

            $reset = PasswordReset::where('email', $request->email)
                                 ->where('code', $request->code)
                                 ->first();

            if (!$reset) {
                return redirect()->back()->with('error', 'کد بازنشانی یا ایمیل نامعتبر است')->withInput();
            }

            if (Carbon::now()->gt($reset->expires_at)) {
                $reset->delete();
                return redirect()->back()->with('error', 'کد بازنشانی منقضی شده است. لطفاً دوباره درخواست کنید.')->withInput();
            }

            return redirect()->route('reset-password')->with('email', $request->email)->with('code', $request->code)->with('success', 'کد بازنشانی تأیید شد. لطفاً رمز عبور جدید را وارد کنید.');
        } catch (\Exception $e) {
            
            return redirect()->back()->with('error', 'خطای سرور: ' . $e->getMessage())->withInput();
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|exists:users,email',
                'code' => 'required|string|size:6',
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors())->withInput()->with('error', 'خطا در اعتبارسنجی');
            }

            $reset = PasswordReset::where('email', $request->email)
                                 ->where('code', $request->code)
                                 ->first();

            if (!$reset) {
                return redirect()->back()->with('error', 'کد بازنشانی یا ایمیل نامعتبر است')->withInput();
            }

            if (Carbon::now()->gt($reset->expires_at)) {
                $reset->delete();
                return redirect()->back()->with('error', 'کد بازنشانی منقضی شده است. لطفاً دوباره درخواست کنید.')->withInput();
            }

            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            $reset->delete();

            return redirect()->route('login')->with('success', 'رمز عبور با موفقیت بازنشانی شد. لطفاً وارد شوید.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطای سرور: ' . $e->getMessage())->withInput();
        }
    }
}