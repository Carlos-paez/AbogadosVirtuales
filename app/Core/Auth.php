<?php

namespace App\Core;

class Auth
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;
    private const SESSION_TIMEOUT = 1800; // 30 minutes
    private const RATE_LIMIT_FILE = __DIR__ . '/../../data/rate_limits.json';

    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
    }

    public static function isLoggedIn(): bool
    {
        self::init();
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        self::init();
        return $_SESSION['user'] ?? null;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && ($user['rol'] ?? '') === 'administrador';
    }

    public static function userAreaId(): ?int
    {
        $user = self::user();
        return $user ? ($user['area_id'] ?? null) : null;
    }

    public static function login(string $credencial, string $password): bool
    {
        self::init();

        if (self::isLockedOut() || self::isIpLockedOut()) {
            return false;
        }

        $user = \App\Models\User::findByCredencial($credencial);
        $dummyHash = '$2y$12$' . bin2hex(random_bytes(16)) . str_repeat('a', 31);
        $hashToVerify = $user ? $user['password_hash'] : $dummyHash;

        $result = password_verify($password, $hashToVerify);

        if ($user && $result) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'nombre' => $user['nombre'],
                'credencial' => $user['credencial'],
                'email' => $user['email'] ?? '',
                'rol' => $user['rol'] ?? 'usuario',
                'area_id' => $user['area_id'] ?? null,
            ];
            $_SESSION['last_activity'] = time();
            unset($_SESSION['login_attempts']);
            self::resetIpAttempts();
            return true;
        }

        self::recordFailedAttempt();
        self::recordIpFailedAttempt();
        return false;
    }

    public static function logout(): void
    {
        self::init();
        $_SESSION = [];
        session_destroy();
    }

    public static function generateCsrfToken(): string
    {
        self::init();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrfToken(string $token): bool
    {
        self::init();
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function getRemainingAttempts(): int
    {
        self::init();
        $attempts = $_SESSION['login_attempts']['count'] ?? 0;
        return max(0, self::MAX_ATTEMPTS - $attempts);
    }

    public static function isLockedOut(): bool
    {
        $attempts = $_SESSION['login_attempts'] ?? null;
        if (!$attempts) {
            return false;
        }
        if (($attempts['count'] ?? 0) >= self::MAX_ATTEMPTS) {
            $lockUntil = $attempts['locked_until'] ?? 0;
            if (time() < $lockUntil) {
                return true;
            }
            unset($_SESSION['login_attempts']);
        }
        return false;
    }

    private static function recordFailedAttempt(): void
    {
        $attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'locked_until' => 0];
        $attempts['count'] = ($attempts['count'] ?? 0) + 1;
        if ($attempts['count'] >= self::MAX_ATTEMPTS) {
            $attempts['locked_until'] = time() + (self::LOCKOUT_MINUTES * 60);
        }
        $_SESSION['login_attempts'] = $attempts;
    }

    public static function getClientIp(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    private static function getRateLimits(): array
    {
        if (file_exists(self::RATE_LIMIT_FILE)) {
            $data = json_decode(file_get_contents(self::RATE_LIMIT_FILE), true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    private static function saveRateLimits(array $limits): void
    {
        file_put_contents(self::RATE_LIMIT_FILE, json_encode($limits, LOCK_EX));
    }

    public static function isIpLockedOut(): bool
    {
        $ip = self::getClientIp();
        $limits = self::getRateLimits();
        if (!isset($limits[$ip])) {
            return false;
        }
        $entry = $limits[$ip];
        if (($entry['count'] ?? 0) >= self::MAX_ATTEMPTS) {
            if (time() < ($entry['locked_until'] ?? 0)) {
                return true;
            }
            unset($limits[$ip]);
            self::saveRateLimits($limits);
        }
        return false;
    }

    public static function recordIpFailedAttempt(): void
    {
        $ip = self::getClientIp();
        $limits = self::getRateLimits();
        if (!isset($limits[$ip])) {
            $limits[$ip] = ['count' => 0, 'locked_until' => 0];
        }
        $limits[$ip]['count'] = ($limits[$ip]['count'] ?? 0) + 1;
        if ($limits[$ip]['count'] >= self::MAX_ATTEMPTS) {
            $limits[$ip]['locked_until'] = time() + (self::LOCKOUT_MINUTES * 60);
        }
        self::saveRateLimits($limits);
    }

    public static function resetIpAttempts(): void
    {
        $ip = self::getClientIp();
        $limits = self::getRateLimits();
        unset($limits[$ip]);
        self::saveRateLimits($limits);
    }

    public static function checkSessionTimeout(): bool
    {
        self::init();
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT) {
                self::logout();
                return true;
            }
        }
        $_SESSION['last_activity'] = time();
        return false;
    }

    public static function refreshSessionUser(): void
    {
        self::init();
        if (!self::isLoggedIn()) return;
        $userId = $_SESSION['user_id'];
        $user = \App\Models\User::findById($userId);
        if ($user) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'nombre' => $user['nombre'],
                'credencial' => $user['credencial'],
                'email' => $user['email'] ?? '',
                'rol' => $user['rol'] ?? 'usuario',
                'area_id' => $user['area_id'] ?? null,
            ];
        }
    }
}
