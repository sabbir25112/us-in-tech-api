<?php namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApiAuthController extends APIController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return $this->getErrorResponse($validator);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'email'     => $request->email,
                'password'  => bcrypt($request->password),
            ]);
            if (!$user) {
                return request()->json([
                    $this->MESSAGE => "could not create new user",
                    $this->SUCCESS => false
                ]);
            }
            DB::commit();

            return $this->login($request);
        } catch (\Exception $exception) {
            DB::rollback();

            return request()->json([
                $this->MESSAGE => "could not create new user",
                $this->SUCCESS => false
            ]);
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $validator = Validator::make(request()->all(), [
            'email'     => 'required|email',
            'password'  => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return $this->getErrorResponse($validator);
        }

        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json([
                $this->MESSAGE  => 'Unauthorized',
                $this->DATA     => null,
                $this->SUCCESS  => false,
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json([
            $this->MESSAGE  => "Successful",
            $this->DATA     => UserResource::make(auth()->user()),
            $this->SUCCESS  => true
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json([
            $this->MESSAGE  => 'Successfully logged out',
            $this->DATA     => null,
            $this->SUCCESS  => true,
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
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
            $this->MESSAGE  => "Successful",
            $this->DATA     => [
                'access_token'  => $token,
                'token_type'    => 'bearer',
                'expires_in'    => auth()->factory()->getTTL() * 60,
                'user'          => UserResource::make(auth()->user()),
            ],
            $this->SUCCESS  => true
        ], 200);
    }
}
