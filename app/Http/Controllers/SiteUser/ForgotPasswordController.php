<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiteUser\ForgotPasswordRequest;
use App\Http\Requests\SiteUser\ResetPasswordRequest;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use App\Models\SiteUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(ForgotPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = $validated['email'];

        $user = SiteUser::where('email', $email)->first();

        if ($user) {
            $token = Str::random(60);

            DB::table('password_resets')->updateOrInsert(
                ['email' => $email],
                ['token' => $token, 'created_at' => Carbon::now()]
            );

            $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
            $resetLink = $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($email);

            Mail::to($user)->queue(new ResetPasswordMail($user, $resetLink));
        }

        return response()->json([
            'message' => 'Jika email Anda terdaftar, link reset password telah dikirim.'
        ]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = $validated['email'];
        $token = $validated['token'];
        $newPassword = $validated['password'];

        $expires = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return DB::transaction(function () use ($email, $token, $newPassword, $expires) {

            $passwordReset = DB::table('password_resets')
                ->where('email', $email)
                ->where('token', $token)
                ->first();

            if (!$passwordReset) {
                return response()->json(['message' => 'Token atau email tidak valid.'], 422);
            }

            if (Carbon::parse($passwordReset->created_at)->addMinutes($expires)->isPast()) {
                DB::table('password_resets')->where('email', $email)->delete();
                return response()->json(['message' => 'Token sudah kadaluarsa.'], 422);
            }

            $user = SiteUser::where('email', $email)->first();
            if (!$user) {
                return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
            }

            $user->password = Hash::make($newPassword);
            $user->save();

            DB::table('password_resets')->where('email', $email)->delete();

            return response()->json(['message' => 'Password berhasil diubah. Silakan login.']);
        });
    }
}
