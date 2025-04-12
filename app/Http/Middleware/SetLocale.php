<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 从会话中获取语言
        $locale = session('locale');
        
        // 如果会话中没有语言，则从请求中获取
        if (!$locale && $request->has('lang')) {
            $locale = $request->input('lang');
        }
        
        // 如果没有语言，则使用默认语言
        if (!$locale) {
            $language = Language::where('is_default', true)->first();
            
            if ($language) {
                $locale = $language->code;
            }
        }
        
        // 检查语言是否存在且激活
        if ($locale) {
            $language = Language::where('code', $locale)->where('is_active', true)->first();
            
            if ($language) {
                App::setLocale($locale);
                session(['locale' => $locale]);
            }
        }
        
        return $next($request);
    }
}
