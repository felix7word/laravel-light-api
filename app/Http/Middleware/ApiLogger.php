<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // 开始时间
        $startTime = microtime(true);
        
        // 记录请求信息
        $this->logRequest($request);
        
        // 处理请求
        $response = $next($request);
        
        // 结束时间
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // 转换为毫秒
        
        // 记录响应信息
        $this->logResponse($request, $response, $executionTime);
        
        return $response;
    }
    
    /**
     * 记录请求信息
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function logRequest(Request $request)
    {
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
        
        // 记录请求参数，排除敏感信息
        if ($request->method() === 'GET') {
            $logData['query'] = $request->query();
        } else {
            $logData['body'] = $this->filterSensitiveData($request->all());
        }
        
        Log::info('API Request', $logData);
    }
    
    /**
     * 记录响应信息
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @param  float  $executionTime
     * @return void
     */
    protected function logResponse(Request $request, Response $response, float $executionTime)
    {
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => $response->getStatusCode(),
            'execution_time' => round($executionTime, 2) . 'ms',
        ];
        
        // 尝试解析响应内容
        try {
            $content = json_decode($response->getContent(), true);
            if (is_array($content)) {
                $logData['response'] = $this->filterSensitiveData($content);
            }
        } catch (\Exception $e) {
            // 响应不是JSON格式，不记录内容
        }
        
        // 根据状态码选择日志级别
        if ($response->getStatusCode() >= 400) {
            Log::error('API Response', $logData);
        } else {
            Log::info('API Response', $logData);
        }
    }
    
    /**
     * 过滤敏感数据
     *
     * @param  array  $data
     * @return array
     */
    protected function filterSensitiveData(array $data)
    {
        $sensitiveKeys = [
            'password', 'password_confirmation', 'token', 'access_token',
            'secret', 'api_key', 'credit_card', 'cvv', 'expiry_date'
        ];
        
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $data[$key] = '*** REDACTED ***';
            } elseif (is_array($value)) {
                $data[$key] = $this->filterSensitiveData($value);
            }
        }
        
        return $data;
    }
}
