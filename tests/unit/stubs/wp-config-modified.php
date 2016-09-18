<?php

/**

 * The base configuration for WordPress

 *

 * The wp-config.php creation script uses this file during the

 * installation. You don't have to use the web site, you can

 * copy this file to "wp-config.php" and fill in the values.

 *

 * This file contains the following configurations:

 *

 * * MySQL settings

 * * Secret keys

 * * Database table prefix

 * * ABSPATH

 *

 * @link https://codex.wordpress.org/Editing_wp-config.php

 *

 * @package WordPress

 */



// ** MySQL settings - You can get this info from your web host ** //

/** The name of the database for WordPress */

define('DB_NAME', 'wordpress');



/** MySQL database username */

define('DB_USER', 'root');



/** MySQL database password */

define('DB_PASSWORD', 'example');



/** MySQL hostname */

define('DB_HOST', 'mysql');



/** Database Charset to use in creating database tables. */

define('DB_CHARSET', 'utf8');



/** The Database Collate type. Don't change this if in doubt. */

define('DB_COLLATE', '');



/**#@+

 * Authentication Unique Keys and Salts.

 *

 * Change these to different unique phrases!

 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}

 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.

 *

 * @since 2.6.0

 */

define('AUTH_KEY',         '346f292ba41623d763429efb1e1dbb320e815348');

define('SECURE_AUTH_KEY',  '4748afdbb744ccbcebe98d5870537c9c12af0e6f');

define('LOGGED_IN_KEY',    'def835c03aa58bc5dc74f7c805c2ce4a93b2db6a');

define('NONCE_KEY',        '78cff631fa03ae93cc180451435f06fd534260d7');

define('AUTH_SALT',        'd3a8adf7db537794292ea4d6ee31e66dd4599fd4');

define('SECURE_AUTH_SALT', '3057bc019e27c35c942a49688e86ae8e883b10aa');

define('LOGGED_IN_SALT',   '34fe845c21f4b3171a58a379d5d339f51e92de8c');

define('NONCE_SALT',       '73dd4e62f1df70d26a7caaaafc847f20ebce9166');



/**#@-*/



/**

 * WordPress Database Table prefix.

 *

 * You can have multiple installations in one database if you give each

 * a unique prefix. Only numbers, letters, and underscores please!

 */

$table_prefix  = 'wp_';



/**

 * For developers: WordPress debugging mode.

 *

 * Change this to true to enable the display of notices during development.

 * It is strongly recommended that plugin and theme developers use WP_DEBUG

 * in their development environments.

 *

 * For information on other constants that can be used for debugging,

 * visit the Codex.

 *

 * @link https://codex.wordpress.org/Debugging_in_WordPress

 */

define('WP_DEBUG', false);



// If we're behind a proxy server and using HTTPS, we need to alert Wordpress of that fact
// see also http://codex.wordpress.org/Administration_Over_SSL#Using_a_Reverse_Proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
	$_SERVER['HTTPS'] = 'on';
}

/* That's all, stop editing! Happy blogging. */



/** Absolute path to the WordPress directory. */

if ( !defined('ABSPATH') )

	define('ABSPATH', dirname(__FILE__) . '/');



/** Sets up WordPress vars and included files. */

#WPDemo Loader
if ( ! defined('DOING_CRON') ) {
  if (defined('DONT_RUN_WPDEMO')) {
    return;
  }
  $_PATHS = require_once('phar://wpdemo/wpdemo.phar/src/autoloader.php');

  $wpdemo_uploads = defined('UPLOADS') ? UPLOADS : 'wp-content/uploads';
  $wpdemo_config  = require_once('wpdemo/Config.php');
  $wpdemo_loader  = new sbacic\wpdemo\Loader(
    $table_prefix,
    $wpdemo_config,
		$_PATHS,
    sbacic\wpdemo\Helper::setupConnection(),

    new sbacic\wpdemo\Cookie()
  );
  $table_prefix   = $wpdemo_loader->getPrefix($wpdemo_loader->getInstance());
}
#WPDemo Loader End
require_once(ABSPATH . 'wp-settings.php');
