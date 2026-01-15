<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\EmailVerification;
use App\Http\Services\User\AuthService;
use App\Http\Resources\User\UserResource;
use App\Http\Requests\User\UserRegisterRequest;
use App\Http\Requests\User\Auth\ResetPasswordRequest;
use App\Http\Requests\User\Auth\EmailVerificationRequest;
use App\Http\Requests\User\Auth\ForgotPasswordRequest;
use App\Exceptions\NotFoundException;
use App\Exceptions\ConflictException;
use Carbon\Carbon;

class AuthController extends Controller
{
    protected $authService;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(AuthService $authService)
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verify', 'forgotPassword', 'resetPassword']]);
        $this->authService = $authService;
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('user')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {

    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = JWTAuth::parseToken()->refresh();

        return $this->respondWithToken($token, 'user');
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60  // Convert TTL to seconds
        ]);
    }

    /**
     * Register a new user
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(UserRegisterRequest $request)
    {
        $conditions['filter']['eq'] = [
            'email' => $request->email,
        ];
        $existingUser = $this->authService->findByConditions($conditions);
        if ($existingUser) {
            throw new ConflictException("The email address is already exist.");
        }

        try {
            $user = $this->authService->register($request->all());
            return response()->json([
                'message' => 'User registered successfully. Please verify your email to activate your account.',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Verify the user's email address
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(EmailVerificationRequest $request)
    {
        $token = $request->input('token');
        $verification = EmailVerification::where('token', $token)->first();
        if (!$verification) {
            return response()->json(['message' => 'Invalid or expired token.'], 400);
        }
        if ($verification->expiration_time < Carbon::now()) {
            return response()->json(['message' => 'Token has expired.'], 400);
        }

        $conditions['filter']['eq'] = [
            'email' => $verification->email,
        ];
        $user = $this->authService->findByConditions($conditions);
        if (!$user) {
            throw new NotFoundException("User not found.");
        }
        $user->is_active = true;
        $user->save();
        $verification->delete();

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Forgot password
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $email = $request->email;
            $conditions['filter']['eq'] = [
                'email' => $email,
            ];
            $user = $this->authService->findByConditions($conditions);
            if (!$user) {
                throw new NotFoundException("User not found.");
            }

            $this->authService->forgotPassword($email);
            return response()->json(['message' => 'Reset password link sent.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * Reset password
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
    */
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $data = $request->all();
            // check token
            $verification = EmailVerification::where('token', $data['token'])
                ->where('email', $data['email'])
                ->first();
            if (!$verification) {
                throw new NotFoundException("Invalid token or email.");
            }
            if ($verification->expiration_time < Carbon::now()) {
                throw new NotFoundException("Token has expired.");
            }

            // check user
            $conditions['filter']['eq'] = [
                'email' => $data['email'],
            ];
            $user = $this->authService->findByConditions($conditions);
            if (!$user) {
                throw new NotFoundException("User not found.");
            }

            $user = $this->authService->recoverPassword($data, $user);
            return response()->json([
                'data' => new UserResource($user),
            ]);
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
