<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectSuspiciousInput
{
    /**
     * Input fields to skip (internal / safe by design).
     */
    private const SKIP_FIELDS = ['_token', '_method', 'password', 'password_confirmation', 'current_password'];

    /**
     * Patterns and their labels.
     * Each entry: [label, regex]
     */
    private const PATTERNS = [
        ['SQL injection',      '/(\bunion\b.{0,30}\bselect\b|\bselect\b.{0,30}\bfrom\b|\bdrop\b.{0,20}\b(table|database)\b|\binsert\b.{0,20}\binto\b|\bdelete\b.{0,20}\bfrom\b|\bupdate\b.{0,20}\bset\b|\'[\s]*or[\s]+[\'"0-9]|--[\s]*$|;\s*--)/i'],
        ['XSS attempt',        '/<\s*(script|iframe|object|embed|svg|img|body|link|meta|style)[^>]*>|javascript\s*:|on(load|error|click|mouse\w+|focus|blur|key\w+|submit|input|change|ready)\s*=/i'],
        ['path traversal',     '/(\.\.[\/\\\\]){2,}|(\/|\\\\)(etc\/passwd|proc\/self|windows\/system32|boot\.ini)/i'],
        ['command injection',  '/[`|;&]\s*(ls|cat|rm|wget|curl|bash|sh|python|perl|php|nc|ncat|nmap|whoami|id|uname|pwd)\b/i'],
        ['template injection', '/\{\{.{0,30}\}\}|\$\{.{0,30}\}|<%=?.{0,30}%>/'],
        ['SSRF attempt',       '/\b(file|dict|gopher|ldap|ftp):\/\//i'],
        ['null byte',          '/\x00/'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Only inspect POST/PUT/PATCH bodies and GET query strings with values
        $inputs = $request->except(self::SKIP_FIELDS);

        foreach ($inputs as $field => $value) {
            if (!is_string($value) || $value === '') {
                continue;
            }

            foreach (self::PATTERNS as [$label, $pattern]) {
                if (preg_match($pattern, $value)) {
                    $truncated = mb_strimwidth($value, 0, 120, '…');
                    ActivityLogger::suspicious(
                        sprintf('Suspicious input (%s) in field "%s": %s', $label, $field, $truncated),
                        $request
                    );
                    break; // one log per field is enough
                }
            }
        }

        return $next($request);
    }
}
