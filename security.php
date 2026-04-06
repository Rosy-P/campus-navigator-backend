<?php
/**
 * Security Utility for Campus Navigator
 * Handles: Sessions, CSRF, Rate Limiting, Role Checks, and Sanitization
 */
// ✅ EXACT origin (NOT *)
header("Access-Control-Allow-Origin: https://navigator-tau-three.vercel.app");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");
// ✅ Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// 1. Secure Cookie & Session Configuration
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', '1');

session_set_cookie_params([
    'lifetime' => 0, // Session cookie
    'path' => '/',
    'domain' => '', // Default to current domain
    'secure' => true, // Required for SameSite=None
    'httponly' => true,
    'samesite' => 'None'
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * Generates a CSRF token and stores it in the session
 */
function generate_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
/**
 * Validates the provided CSRF token against the one in session
 */
function validate_csrf_token($token)
{
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
/**
 * Simple Rate Limiting for Login
 * Max 5 attempts per minute
 */
function check_rate_limit($user_email)
{
    $limit = 5;
    $time_window = 60; // 1 minute
    $current_time = time();
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }
    // Clean up old attempts
    $_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], function ($timestamp) use ($current_time, $time_window) {
        return ($current_time - $timestamp) < $time_window;
    });
    if (count($_SESSION['login_attempts']) >= $limit) {
        return false;
    }
    $_SESSION['login_attempts'][] = $current_time;
    return true;
}
/**
 * Role-Based Access Control Check
 */
function require_role($allowed_roles)
{
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowed_roles)) {
        send_error_response("Unauthorized access", 403);
    }
}
/**
 * Centralized Error Response (Hides technical details)
 */
function send_error_response($message = "Something went wrong.", $code = 500)
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error",
        "message" => $message
    ]);
    exit();
}
/**
 * Input Sanitization
 */
function sanitize_input($input)
{
    if (is_array($input)) {
        return array_map('sanitize_input', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}
/**
 * Email Validation
 */
function is_valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
/**
 * Password strength validation (8+ chars, 1 number, 1 letter)
 */
function is_strong_password($password)
{
    return strlen($password) >= 8 &&
        preg_match('/[A-Za-z]/', $password) &&
        preg_match('/[0-9]/', $password);
}
?>