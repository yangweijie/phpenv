<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\ActivityLog;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        
        // 记录登录信息
        $user->recordLogin();
        
        // 记录活动日志
        ActivityLog::logSystem(
            "用户 [{$user->name}] 登录成功", 
            ['email' => $user->email, 'ip' => request()->ip()], 
            ActivityLog::TYPE_INFO
        );
    }
}
