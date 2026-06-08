<?php
/**
 * Root index.php redirection helper.
 * Redirects root-level requests to the secure /public directory.
 */
header('Location: public/');
exit;
