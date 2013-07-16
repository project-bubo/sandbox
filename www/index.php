<?php

// absolute filesystem path to the web root
define('WWW_DIR', __DIR__);

// absolute filesystem path to the application root
define('APP_DIR', WWW_DIR . '/../app');

// absolute filesystem path to the temporary files
define('TEMP_DIR', WWW_DIR . '/../temp');

// absolute filesystem path to the temporary files
define('LIBS_DIR', WWW_DIR . '/../libs');

// absolute filesystem path to the temporary files
define('PLUGINS_DIR', WWW_DIR . '/../plugins');

define('DATA_DIR', WWW_DIR . '/../data');

define('CONFIG_DIR', APP_DIR . '/config');

// absolute filesystem path to the web root
define('DEFAULT_LANGUAGE', 'cs');

// load bootstrap file
require APP_DIR . '/bootstrap.php';
