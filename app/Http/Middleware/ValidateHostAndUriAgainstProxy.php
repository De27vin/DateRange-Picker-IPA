<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;

class ValidateHostAndUriAgainstProxy
{
    public function handle(Request $request, Closure $next)
    {
        $incomingHost = $request->getHost();
        $allowedHosts = config('app.allowed_hosts', []);
        $proxyAttempt = false;

        if (!$this->isHostAllowed($incomingHost, $allowedHosts)) {
            $proxyAttempt = true;
        }

        // Check REQUEST_URI for proxy attempts
        $requestUri = $request->server('REQUEST_URI');
        if (preg_match('#^https?://#i', $requestUri)) {
            $requestedHost = parse_url($requestUri, PHP_URL_HOST);
            if ($requestedHost && !$this->isHostAllowed($requestedHost, $allowedHosts)) {
                $proxyAttempt = true;
            }
        }

        if ($proxyAttempt) {
            $this->logProxyAttempt($request);
            throw new HttpException(403, 'Proxy attempts are not allowed');
        }

        return $next($request);
    }

    /**
     * Check if the incoming host is in the allowed hosts list
     *
     * @param string $incomingHost
     * @param array $allowedHosts
     * @return bool
     */
    private function isHostAllowed(string $incomingHost, array $allowedHosts): bool
    {
        // If no hosts are configured, reject all (fail-safe)
        if (empty($allowedHosts)) {
            return false;
        }

        // Normalize incoming host to lowercase
        $incomingHost = strtolower($incomingHost);

        // Check against each allowed host
        foreach ($allowedHosts as $allowedHost) {
            // Skip empty entries
            if (empty($allowedHost)) {
                continue;
            }

            // Normalize allowed host to lowercase
            $allowedHost = strtolower(trim($allowedHost));

            // Exact match
            if ($incomingHost === $allowedHost) {
                return true;
            }
        }

        return false;
    }
    private function logProxyAttempt(Request $request)
    {
        // Get the client IP address
        $ip = $request->getClientIp();

        // Create or read the existing IP list file
        $logFile = storage_path('logs/proxy_attempts_ip.log');
        $ipList = [];

        if (file_exists($logFile)) {
            $ipList = json_decode(file_get_contents($logFile), true) ?? [];
        }

        // Add the IP to the list if it doesn't already exist
        if (!in_array($ip, $ipList)) {
            $ipList[] = $ip;
            file_put_contents($logFile, json_encode($ipList));
            chmod($logFile, 0600);
            Log::channel('proxy')->info("Proxy attempt detected", [
                'ip' => $ip,
                'host' => $request->getHost(),
                'uri' => $request->server('REQUEST_URI'),
                'user_agent' => $request->header('User-Agent')
            ]);
        }
    }
}