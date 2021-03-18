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


class GameController extends BaseController
{
	/**
	* @OA\Post(
	* path="/api/v1/endgame",
	* summary="End Game",
	* description="Players End Game",
	* operationId="endGame",
	* tags={"Game"},
	* @OA\RequestBody(
	*    required=true,
	*    description="Players End Game",
	*    @OA\JsonContent(
	*      	@OA\Property(property="Players", type="array", collectionFormat="multi",
	*	      	@OA\Items(
	*	           	@OA\Property(property="id", type="string", format="username", example="3"),
	*	       		@OA\Property(property="score", type="string", format="password", example="10"),
	*       	)
	*       ),
	*    ),
	* ),
	* @OA\Response(
	*    response=400,
	*    description="Validation",
	*    @OA\JsonContent(
	*    @OA\Property(property="status", type="string", example="error"),
	*       @OA\Property(
	*              property="errors",
	*              type="array",
	*              collectionFormat="multi",
	*              @OA\Items(
	*                 type="string",
	*                 example="Player not found",
	*              )
	*           )
	*        )
	*     )
	* )
	*/
	public function endGame(Request $request)
	{
		$result = [];
		$players =  $request->json()->all();
		if(!isset($players['Players']) || empty($players['Players'])){
            return ApiResponse::generate('Players can not be empty', 400);
        }
        
        foreach ($players['Players'] as $key => $player) {
            $username = Redis::hget('user:'.$player['id'],'username');
            //player exists
            if(!$username){
            	return ApiResponse::generate('Player not found', 400);
            }
           
            $players['Players'][$key]['username'] = $username;
		}

		foreach($players['Players'] as $key => $player){
			$score = Redis::zIncrBy('scores', $player['score'], $player['username'].':'.$player['id']);
			$result[] =['id' => (int)$player['id'], 'username' => $player['username'], 'score' => $score];
		}

		return ApiResponse::generate($result);
	}
}
