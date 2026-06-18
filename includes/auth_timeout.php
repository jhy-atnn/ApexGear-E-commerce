<?php
const APEX_LOGIN_TIMEOUT_SECONDS = 3600;

function apex_set_login_timeout(): void
{
    $_SESSION['apex_login_expires_at'] = time() + APEX_LOGIN_TIMEOUT_SECONDS;
    if (!headers_sent()) {
        setcookie('apex_logged_in', (string) time(), time() + APEX_LOGIN_TIMEOUT_SECONDS, '/');
    }
}

function apex_clear_login_timeout(): void
{
    unset($_SESSION['apex_login_expires_at']);
    if (!headers_sent()) {
        setcookie('apex_logged_in', '', time() - 3600, '/');
    }
}

function apex_expire_user_session(): void
{
    unset($_SESSION['user'], $_SESSION['favorites']);
    apex_clear_login_timeout();
}

function apex_enforce_login_timeout(): bool
{
    if (!isset($_SESSION['user'])) {
        return false;
    }

    $expiresAt = isset($_SESSION['apex_login_expires_at']) ? (int) $_SESSION['apex_login_expires_at'] : 0;
    if ($expiresAt > 0 && time() >= $expiresAt) {
        apex_expire_user_session();
        return true;
    }

    return false;
}
