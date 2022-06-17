<?php 

/**
 * To run this use the following command
 * php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/logger_test.php 
 */

$message = "test message";
append_log($message, $log_type = "info", $filename = 'test_logs');
append_log($message, $log_type = "error", $filename = 'test_logs');
append_log($message, $log_type = "error");
append_log($message, $log_type = "info");