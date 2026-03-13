<?php

namespace App\Services;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Request;

class LogService
{
    public static function write(
        string $level,
        string $type,
        string $subject,
        array  $context  = [],
        ?string $userId  = null
    ): SystemLog {
        return SystemLog::create([
            'level'      => $level,
            'type'       => $type,
            'user_id'    => $userId ?? (auth()->check() ? auth()->id() : null),
            'subject'    => $subject,
            'context'    => $context,
            'ip'         => Request::ip(),
            'user_agent' => substr(Request::userAgent() ?? '', 0, 255),
        ]);
    }

    // Shorthand helpers
    public static function info(string $type, string $subject, array $ctx = [], ?string $uid = null): SystemLog
    { return self::write('info', $type, $subject, $ctx, $uid); }

    public static function warning(string $type, string $subject, array $ctx = [], ?string $uid = null): SystemLog
    { return self::write('warning', $type, $subject, $ctx, $uid); }

    public static function error(string $type, string $subject, array $ctx = [], ?string $uid = null): SystemLog
    { return self::write('error', $type, $subject, $ctx, $uid); }

    public static function payment(string $type, string $subject, array $ctx = [], ?string $uid = null): SystemLog
    { return self::write('payment', $type, $subject, $ctx, $uid); }

    public static function subscription(string $type, string $subject, array $ctx = [], ?string $uid = null): SystemLog
    { return self::write('subscription', $type, $subject, $ctx, $uid); }

    public static function auth(string $type, string $subject, array $ctx = [], ?string $uid = null): SystemLog
    { return self::write('auth', $type, $subject, $ctx, $uid); }
}
