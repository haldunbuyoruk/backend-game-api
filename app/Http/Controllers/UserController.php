<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use ApiResponse;
use Validator;
use Illuminate\Support\Facades\Redis;

/**
 * @group  User management
 *
 * APIs for singup and login users
 */

class UserController extends BaseController
{

	
	
	/**
	* @OA\Post(
	* path="/api/v1/user/signin",
	* summary="Login",
	* description="Login by username, password",
	* operationId="signIn",
	* tags={"User"},
	* @OA\RequestBody(
	*    required=true,
	*    description="User Login",
	*    @OA\JsonContent(
	*       required={"username","password"},
	*       @OA\Property(property="username", type="string", format="username", example="haldun"),
	*       @OA\Property(property="password", type="string", format="password", example="qwq123qwq"),
	*    ),
	* ),
	* * @OA\Response(
	*    response=400,
	*    description="Validation",
	*    @OA\JsonContent(
	*    	@OA\Property(property="status", type="string", example="error"),
	*       @OA\Property(
	*              property="errors",
	*              type="array",
	*              collectionFormat="multi",
	*              @OA\Items(
	*                type="string",
	*                example="The password must be at least 5 characters."
	*              )
	*           )
	*    )
	* )
	*)			z
	*/
	public function signIn(Request $request)
	{
		$user =  $request->json()->all();
		$rules = [
			'username' => 'required|regex:/^\w{1,}$/',
			'password' => 'required|min:5'
		];
		$validator = Validator::make($user, $rules);
		if(!$validator->passes()){
			return ApiResponse::generate($validator->errors()->all(), 400);
		}

		$userId = Redis::hGet('users', $user['username']);
		if(!$userId){
			return ApiResponse::generate('Incorrect username or password', 401);
		}
		
		$password = Redis::hGet('user:'.$userId, 'password');
		if(!$password){
			return ApiResponse::generate('Incorrect username or password', 401);
		}

		if($password != md5($user['password'])){
			return ApiResponse::generate('Incorrect username or password', 401);
		}
		
		return ApiResponse::generate(['id' => (int)$userId, 'username' => $user['username']]);

	}

	/**
	* @OA\Post(
	* path="/api/v1/user/signup",
	* summary="Sign Up",
	* description="Sign up by username, password",
	* operationId="signUp",
	* tags={"User"},
	* @OA\RequestBody(
	*    required=true,
	*    description="User Sign Up",
	*    @OA\JsonContent(
	*       required={"username","password"},
	*       @OA\Property(property="username", type="string", format="username", example="haldun"),
	*       @OA\Property(property="password", type="string", format="password", example="qwq123qwq"),
	*    ),
	* ),
	* @OA\Response(
	*    response=409,
	*    description="User allready exist",
	*    @OA\JsonContent(
	*    	@OA\Property(property="status", type="string", example="error"),
	*       @OA\Property(
*              property="errors",
*              type="array",
*              collectionFormat="multi",
*              @OA\Items(
*                 type="string",
*                 example="User allready exist",
*              )
*           )
	*    )
	* )
	* )
	*/
	public function signUp(Request $request)
	{
		$user = $request->json()->all();
		$rules = [
			'username' => 'required|regex:/^\w{1,}$/',
			'password' => 'required|min:5'
		];
		$validator = Validator::make($user, $rules);
		if(!$validator->passes()){
			return ApiResponse::generate($validator->errors()->all(), 400);
		}
		
		$result = Redis::hGet('users', $user['username']);
		if(!empty($result)){
			return ApiResponse::generate(['User allready exist'], 409);
		}

		$userId = Redis::incr('global:nextUserId');
		if(!Redis::hSet("users", $user['username'], $userId)){
			return ApiResponse::generate(['Could not save user'], 500);
		}

		$user['password'] = md5($user['password']);
		if(!Redis::hmSet("user:".$userId, $user)){
			return ApiResponse::generate(['Could not save user'], 500);
		}
		$user['id'] = (int)$userId;

		return ApiResponse::generate($user);
	}
}
