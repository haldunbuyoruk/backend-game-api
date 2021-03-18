<?php

namespace App\Helpers;

use Symfony\Component\HttpFoundation\Response as HttpResponse;

use Illuminate\Http\JsonResponse;

Class ApiResponse
{
	
	public static function generate($data, $statusCode = 200): JsonResponse
	{
       	return Response()->json(self::createResponseData($data, $statusCode), $statusCode);
	}

	public static function createResponseData($data, $statusCode)
	{
		$result = [];
		$httpResponse = new HttpResponse();
		$httpResponse->setStatusCode($statusCode);
		if($httpResponse->isSuccessful()){
			$result['status'] = 'success';
			$result['timestamp'] = strtotime('now');
			$result['result'] = $data;
			return $result;

		}
		
		if($httpResponse->isClientError()){
			$result['status'] = 'error';
			if(is_array($data)){
				$result['errors'] = $data;
			}else{
				$result['errors'] = [$data];
			}
			return $result;
	
		}
	}
}