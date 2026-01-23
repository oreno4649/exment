<?php

/**
 * PHPStan bootstrap file
 * This file sets environment flags to prevent plugin loading conflicts during static analysis.
 */

// Set a flag to indicate we're running in PHPStan context
if (!defined('PHPSTAN_RUNNING')) {
    define('PHPSTAN_RUNNING', true);
}

// Prevent duplicate class loading issues
putenv('EXMENT_SKIP_PLUGIN_LOAD=true');
$_ENV['EXMENT_SKIP_PLUGIN_LOAD'] = 'true';
