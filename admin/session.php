<?php
// Shared admin session configuration for all admin pages.
$sessionDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0777, true);
}
if (session_status() === PHP_SESSION_NONE) {
    session_save_path($sessionDir);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
?>
