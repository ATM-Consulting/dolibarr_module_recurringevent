<?php

const DOL_ROOT = '/var/www/html';

global $conf;
// FIXME: Missing global config => create an error when trying to connect

define('NOREDIRECTBYMAINTOLOGIN', 1);
define('TEST_ENV_SETUP', 1);
//const NOLOGIN = true;
require_once DOL_ROOT . '/main.inc.php';

require_once DOL_ROOT . '/core/lib/admin.lib.php';

// Require tested class
const MODULE_ROOT = DOL_ROOT . '/custom/recurringevent';

if (!defined('DB_HOST')) {
	define('DB_HOST', $dolibarr_main_db_host);
	define('DB_NAME', $dolibarr_main_db_name);
	define('DB_USER', $dolibarr_main_db_user);
	define('DB_PASS', $dolibarr_main_db_pass);
	define('DB_DRIVER', $dolibarr_main_db_type);
}