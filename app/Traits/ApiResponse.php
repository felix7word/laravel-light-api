<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

 trait ApiResponse
 {
     /**
      * Return a success response
      * 
      * @param  mixed  $data
      * @param  string  $message
      * @param  int  $statusCode
      * @return JsonResponse
      */
     public function success($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
     {
         return response()->json([
             'success' => true,
             'message' => $message,
             'data' => $data,
         ], $statusCode);
     }

     /**
      * Return an error response
      * 
      * @param  string  $message
      * @param  int  $statusCode
      * @param  mixed  $errors
      * @return JsonResponse
      */
     public function error(string $message = 'Error', int $statusCode = 400, $errors = null): JsonResponse
     {
         return response()->json([
             'success' => false,
             'message' => $message,
             'errors' => $errors,
         ], $statusCode);
     }

     /**
      * Return a pagination response
      * 
      * @param  mixed  $data
      * @param  string  $message
      * @return JsonResponse
      */
     public function paginate($data, string $message = 'Success'): JsonResponse
     {
         return response()->json([
             'success' => true,
             'message' => $message,
             'data' => $data->items(),
             'meta' => [
                 'total' => $data->total(),
                 'per_page' => $data->perPage(),
                 'current_page' => $data->currentPage(),
                 'last_page' => $data->lastPage(),
                 'from' => $data->firstItem(),
                 'to' => $data->lastItem(),
             ],
             'links' => [
                 'first' => $data->url(1),
                 'last' => $data->url($data->lastPage()),
                 'prev' => $data->previousPageUrl(),
                 'next' => $data->nextPageUrl(),
             ],
         ]);
     }
 }