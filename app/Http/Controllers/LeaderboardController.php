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


class LeaderboardController extends BaseController
{

	/**
	* @OA\GET(
	* path="/api/v1/leaderboard",
	* summary="Leaderboard",
	* description="Leaderboard",
	* operationId="leaderboard",
	* tags={"Leaderboard"},
	* @OA\Response(
	* 		response=200,
	* 		description="Leaderboard",

		* 		@OA\Property(property="status", type="string", example="success"),
		* 		@OA\Property(property="timestamp", type="integer", example="12313213"),
		* 		@OA\Property(property="result", type="array", collectionFormat="multi",
		*	      	@OA\Items(
		*	           	@OA\Property(property="id", type="string", format="username", example="3"),
		*	           	@OA\Property(property="username", type="string", format="username", example="haldun"),
		*	       		@OA\Property(property="rank", type="integer", format="password", example="10"),
		*       	),
	*       	),
	* ),
	* )
	*
	* 
	*/
	
	public function list(Request $request)
	{
		$limit = $request->get('limit');	
		if(!$limit){
			$limit = Redis::zCard('scores');
		}

		$scores = Redis::zRevRange('scores',0, $limit, true);
		$result = [];
		foreach ($scores as $key => $value) {
			$user = explode(':',$key);
			$result[] = ['id' => (int)$user[1], 'username' => $user[0], 'rank' => $value];	
		}
		return ApiResponse::generate($result);
	}
	
}
