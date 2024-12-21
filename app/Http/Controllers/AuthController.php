<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $nodejsUrl;

    public function __construct()
    {
        $this->nodejsUrl = config('app.nodejs_api_url');
    }

    public function profile(Request $request)
    {
        try {
            $authHeader = $request->header('Authorization');

            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                throw new Exception('Bearer token not provided or invalid format.');
            }

            $response = Http::withHeaders([
                'Authorization' => $authHeader,
            ])->get("{$this->nodejsUrl}/auth/profile");

            return $response->json();

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Internal Server Error",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $response = new BaseResponse();

        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                $response->success = false;
                $response->message = 'there was something wrong with his request';
                $response->data = null;
                return response()->json($response->toArray(), 400);
            }

            $data = $validator->validated();

            $response = Http::post("{$this->nodejsUrl}/auth/login", [
                'username' => $data['username'],
                'password' => $data['password']
            ]);

            $refreshToken = $response->cookies()->getCookieByName('refreshToken');

            if ($refreshToken) {
                $cookie = cookie('refreshToken', $refreshToken->getValue(), 60, null, null, true, true);
                return response()->json($response->json(), $response->status())->cookie($cookie);
            }

            return response()->json($response->json(), $response->status());

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $refreshToken = $request->cookie('refreshToken');

            // return $refreshToken;

            if (!$refreshToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: No refresh token found.'
                ], 401);
            }

            $response = Http::withCookies(
                ['refreshToken' => $refreshToken],
                parse_url($this->nodejsUrl, PHP_URL_HOST)
            )->delete("{$this->nodejsUrl}/auth/logout");

            $cookie = Cookie::forget('refreshToken');

            return response()->json($response->json(), $response->status())->cookie($cookie);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Internal Server Error",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function accessToken(Request $request)
    {
        try {
            $refreshToken = $request->cookie('refreshToken');

            if (!$refreshToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: No refresh token found.'
                ], 401);
            }

            $response = Http::withCookies(
                ['refreshToken' => $refreshToken],
                parse_url($this->nodejsUrl, PHP_URL_HOST)
            )->get("{$this->nodejsUrl}/auth/refresh-token");

            return $response->json();

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Internal Server Error",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePassword()
    {
        
    }
}
