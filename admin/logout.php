<?php
/**
 * logout.php
 * Khadomeh Admin Panel Logout
 * 
 * Clears the session array, deletes the session cookie,
 * destroys the session, and redirects to the login screen.
 */

// Include system bootstrapper
require_once dirname(__DIR__) . '/includes/init.php';

use App\Core\Response;

// 1. Unset all session variables
$_SESSION = [];

// 2. Delete the session cookie if active
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. Destroy the session on server
session_destroy();

// 4. Redirect to login page
Response::redirect(admin_url('login.php'));
