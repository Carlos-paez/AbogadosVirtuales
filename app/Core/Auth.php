<?php

namespace App\Core;

class Auth
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

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

    public static function login(string $username, string $password): bool
    {
        self::init();

        if (self::isLockedOut()) {
            return false;
        }

        $user = \App\Models\User::findByUsername($username);
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'nombre' => $user['nombre']
            ];
            unset($_SESSION['login_attempts']);
            return true;
        }

        self::recordFailedAttempt();
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
}
