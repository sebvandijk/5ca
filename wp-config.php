<?php

// BEGIN iThemes Security - Do not modify or remove this line
// iThemes Security Config Details: 2
define( 'DISALLOW_FILE_EDIT', true ); // Disable File Editor - Security > Settings > WordPress Tweaks > File Editor
// END iThemes Security - Do not modify or remove this line

/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information by
 * visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'com5ca-mahrevici');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'fkJsk0GDawyMgZVWTJ3WH1MqctMVrTyVtVNcxVwF5TL9O1hk5FMH2RrbO16PI5gQ6sJFp/20qHQluCbgxVuE+Q==');
define('SECURE_AUTH_KEY',  'rxklqBzlZ5KQRAEqPdGcJNf99JghqnSCRRb7SmR0qOAl+TJ+gHhwfkl6dFKN1geZKWdbO/u9gdzzIww/YpBDCQ==');
define('LOGGED_IN_KEY',    'WZ3fFMEGIzcd5N6AdvM4O+O75RJz9G5Mvtd8pn5/8lJ2mUwj5lVIZSP2Wxru25pLOC8jcLaJlr3dJOE3uKYiAw==');
define('NONCE_KEY',        'hUJRiq3q5TVMHSUfoBrNfyRdDzI89Dgj8esJ02VVyxChCVFbrhcUIzybHT0G4vgn4yL/wVRX8EeJuUnYHEfSlg==');
define('AUTH_SALT',        'd0hAeSzF/ULiXyP5wXJAhTIjJWCQF1cpbQt0TpcmkYp1U0O5LvOO7Biv4LtfiB9jGoee0vYtCDGYcvjR+LIqRw==');
define('SECURE_AUTH_SALT', 'n57LOP0SATQ97f92zeEx2fCaNsw4Tu1L5QT1ThrjZ8xhPH3waP16QWOyEF3D45749kCX3lf53YaSfZPo99hRrA==');
define('LOGGED_IN_SALT',   'nP1MyEjHilSsMLYnxwUuce3up321opbslP8XZpsRllW/bv5NsUBzuyQjVYkPK+kXDnT+nNldhNgmDtioXPgVGQ==');
define('NONCE_SALT',       'FDNDDoNXm9ImlPxsihguBdilUekfbjn/PBAn8tXEhhnHeBv7slbJIl7t3B/g56z48z7tF0P6mQaAawk8zA7PWg==');
/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress.  A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de.mo to wp-content/languages and set WPLANG to 'de' to enable German
 * language support.
 */
define ('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);
define('SAVEQUERIES', false);

/* That's all, stop editing! Happy blogging. */

/** WordPress absolute path to the Wordpress directory. */
if ( !defined('ABSPATH') )
    define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
