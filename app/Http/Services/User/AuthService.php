<?php

namespace App\Http\Services\User;

use App\Repositories\Contracts\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationMail;
use App\Mail\ForgotPasswordMail;
use App\Models\User;
use App\Models\EmailVerification;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Exceptions\NotFoundException;
use App\Http\Services\BaseService;

class AuthService extends BaseService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Register a new user
     *
     * @param array $data
     * @return User
     */
    public function register(array $data)
    {
        DB::beginTransaction();
        try {
            $data['password'] = Hash::make($data['password']);
            $data['is_active'] = false;
            $data['role'] = User::ROLE_USER;
            $user = parent::store($data);
            // email verification
            $verification = EmailVerification::create([
                'email' => $user->email,
                'token' => Str::random(40),
                'expiration_time' => Carbon::now()->addMinutes((int) env('EMAIL_VERIFICATION_EXPIRE_MINUTES', 30)),
            ]);
            // send email verification
            Mail::to($user->email)->queue(new EmailVerificationMail($verification->token, $verification->expiration_time));
            DB::commit();

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle forgot password logic: generate token, store it, and send email.
     *
     * @param string $email
     * @throws \Exception if user not found or email sending fails
     * @return void
     */
    public function forgotPassword(string $email)
    {
        DB::beginTransaction();
        try {
            $token = Str::random(64);
            $expiration = Carbon::now()->addMinutes((int) env('EMAIL_VERIFICATION_EXPIRE_MINUTES', 30));
            EmailVerification::create([
                'email' => $email,
                'token' => $token,
                'expiration_time' => $expiration,
            ]);
            // Send email
            Mail::to($email)->queue(new ForgotPasswordMail($token, $email, $expiration));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reset the user's password and delete the used token.
     *
     * @param array $data Includes 'token' and 'password'
     * @param User $user The user to update
     * @return User
     */
    public function recoverPassword(array $data, User $user)
    {
        $data['password'] = Hash::make($data['password']);
        $user = parent::update($data, $user);

        EmailVerification::where('email', $user->email)
            ->where('token', $data['token'])
            ->delete();

        return $user;
    }
}
