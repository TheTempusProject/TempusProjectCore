<?php
# Directories
	// Tempus Project Core Specific
		if (!defined('TPC_ROOT_DIRECTORY')) {
			define('TPC_ROOT_DIRECTORY', dirname(__DIR__) . DIRECTORY_SEPARATOR);
		}
		if (!defined('TPC_CONFIG_DIRECTORY')) {
			define('TPC_CONFIG_DIRECTORY', TPC_ROOT_DIRECTORY . 'config' . DIRECTORY_SEPARATOR);
		}
		if (!defined('TPC_BIN_DIRECTORY')) {
			define('TPC_BIN_DIRECTORY', TPC_ROOT_DIRECTORY . 'bin' . DIRECTORY_SEPARATOR);
		}
		if (!defined('TPC_RESOURCES_DIRECTORY')) {
			define('TPC_RESOURCES_DIRECTORY', TPC_ROOT_DIRECTORY . 'resources' . DIRECTORY_SEPARATOR);
		}
		if (!defined('TPC_VIEW_DIRECTORY')) {
			define('TPC_VIEW_DIRECTORY', TPC_ROOT_DIRECTORY . 'views' . DIRECTORY_SEPARATOR);
		}
		if (!defined('TPC_ERRORS_DIRECTORY')) {
			define('TPC_ERRORS_DIRECTORY', TPC_VIEW_DIRECTORY . 'errors' . DIRECTORY_SEPARATOR);
		}
		if (!defined('TPC_CLASSES_DIRECTORY')) {
			define('TPC_CLASSES_DIRECTORY', TPC_ROOT_DIRECTORY . 'classes' . DIRECTORY_SEPARATOR);
		}
		if (!defined('TPC_FUNCTIONS_DIRECTORY')) {
			define('TPC_FUNCTIONS_DIRECTORY', TPC_ROOT_DIRECTORY . 'functions' . DIRECTORY_SEPARATOR);
		}
	// Defaults for others
		if (!defined('APP_ROOT_DIRECTORY')) {
			define('APP_ROOT_DIRECTORY', TPC_ROOT_DIRECTORY);
		}
		if (!defined('CONFIG_DIRECTORY')) {
			define('CONFIG_DIRECTORY', TPC_CONFIG_DIRECTORY);
		}
		if (!defined('BIN_DIRECTORY')) {
			define('BIN_DIRECTORY', TPC_BIN_DIRECTORY);
		}
		if (!defined('VIEW_DIRECTORY')) {
			define('VIEW_DIRECTORY', TPC_VIEW_DIRECTORY);
		}
		if (!defined('ERRORS_DIRECTORY')) {
			define('ERRORS_DIRECTORY', TPC_ERRORS_DIRECTORY);
		}
		if (!defined('CLASSES_DIRECTORY')) {
			define('CLASSES_DIRECTORY', TPC_CLASSES_DIRECTORY);
		}
		if (!defined('FUNCTIONS_DIRECTORY')) {
			define('FUNCTIONS_DIRECTORY', TPC_FUNCTIONS_DIRECTORY);
		}
		if (!defined('RESOURCES_DIRECTORY')) {
			define('RESOURCES_DIRECTORY', TPC_RESOURCES_DIRECTORY);
		}
		if (!defined('TEMPLATE_DIRECTORY')) {
			define('TEMPLATE_DIRECTORY', TPC_ROOT_DIRECTORY . 'templates' . DIRECTORY_SEPARATOR);
		}
		if (!defined('IMAGE_UPLOAD_DIRECTORY')) {
			define('IMAGE_UPLOAD_DIRECTORY', TPC_ROOT_DIRECTORY . 'images' . DIRECTORY_SEPARATOR);
		}
		if (!defined('UPLOAD_DIRECTORY')) {
			define('UPLOAD_DIRECTORY', TPC_ROOT_DIRECTORY . 'uploads' . DIRECTORY_SEPARATOR);
		}

# Tempus Debugger
	if (!defined('TEMPUS_DEBUGGER_SECURE_HASH')) {
		define('TEMPUS_DEBUGGER_SECURE_HASH', '');
	}
	if (!defined('TEMPUS_DEBUGGER_SHOW_LINES')) {
		define('TEMPUS_DEBUGGER_SHOW_LINES', false);
	}

# Debug
	if (!defined('DEBUG_ENABLED')) {
		define('DEBUG_ENABLED', false);
	}
	if (!defined('REDIRECTS_ENABLED')) {
		define('REDIRECTS_ENABLED', true);
	}
	if (!defined('RENDERING_ENABLED')) {
		define('RENDERING_ENABLED', true);
	}
	if (!defined('DEBUG_TRACE_ENABLED')) {
		define('DEBUG_TRACE_ENABLED', false);
	}
	if (!defined('DEBUG_TO_CONSOLE')) {
		define('DEBUG_TO_CONSOLE', false);
	}

# Check
	if (!defined('MINIMUM_PHP_VERSION')) {
		define('MINIMUM_PHP_VERSION', 5.6);
	}
	if (!defined('DATA_TITLE_PREG')) {
		define('DATA_TITLE_PREG', '#^[a-z 0-9\-\_ ]+$#mi');
	}
	if (!defined('PATH_PREG_REQS')) {
		define('PATH_PREG_REQS', '#^[^/?*:;\\{}]+$#mi');
	}
	if (!defined('SIMPLE_NAME_PREG')) {
		define('SIMPLE_NAME_PREG', '#^[a-zA-Z0-9\-\_]+$#mi');
	}
	if (!defined('ALLOWED_IMAGE_UPLOAD_EXTENTIONS')) {
		define('ALLOWED_IMAGE_UPLOAD_EXTENTIONS', [".jpg",".jpeg",".gif",".png"]);
	}

# Token
	if (!defined('DEFAULT_TOKEN_NAME')) {
		define('DEFAULT_TOKEN_NAME', 'TPC_SESSION_TOKEN');
	}
	if (!defined('TOKEN_ENABLED')) {
		define('TOKEN_ENABLED', true);
	}

# Database
	if (!defined('MAX_RESULTS_PER_PAGE')) {
		define('MAX_RESULTS_PER_PAGE', 50);
	}
	if (!defined('DEFAULT_RESULTS_PER_PAGE')) {
		define('DEFAULT_RESULTS_PER_PAGE', 5);
	}

# Cookies
	if (!defined('DEFAULT_COOKIE_EXPIRATION')) {
		define('DEFAULT_COOKIE_EXPIRATION', 604800);
	}
	if (!defined('DEFAULT_COOKIE_PREFIX')) {
		define('DEFAULT_COOKIE_PREFIX', 'TPC_');
	}

# Sessions
	if (!defined('DEFAULT_SESSION_PREFIX')) {
		define('DEFAULT_SESSION_PREFIX', 'TPC_');
	}

# Other
	if (!defined('DEFAULT_CONTROLER_CLASS')) {
		define('DEFAULT_CONTROLER_CLASS', 5.6);
	}
	if (!defined('DEFAULT_CONTROLER_METHOD')) {
		define('DEFAULT_CONTROLER_METHOD', 5.6);
	}

# Random
	if (!defined('KB')) {
		define('KB', 1024);
	}
	if (!defined('MB')) {
		define('MB', 1048576);
	}
	if (!defined('GB')) {
		define('GB', 1073741824);
	}
	if (!defined('TB')) {
		define('TB', 1099511627776);
	}

# Tell the app all constants have been loaded.
	define('TEMPUS_CORE_CONSTANTS_LOADED', true);
