<?php
/**
 * auth.php
 * Authentication Configurations
 */

use App\Core\Env;

return [
    'dev_provider_login' => Env::get('DEV_PROVIDER_LOGIN', false),
    'dev_provider_password' => Env::get('DEV_PROVIDER_PASSWORD', 'dev_pass_123'),
    'dev_user_login' => Env::get('DEV_USER_LOGIN', false),
    'dev_user_password' => Env::get('DEV_USER_PASSWORD', 'user_pass_123'),
];
