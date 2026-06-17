<?php
/**
 * auth.php
 * Authentication Configurations
 */

use App\Core\Env;

return [
    'dev_provider_login' => Env::get('DEV_PROVIDER_LOGIN', false),
    'dev_provider_password' => Env::get('DEV_PROVIDER_PASSWORD', 'dev_pass_123'),
];
