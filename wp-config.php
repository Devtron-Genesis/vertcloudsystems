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
define( 'DB_NAME', 'vertcloudsystem' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'Devtron1234#' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Y8mxg1@gw9:o AjA]yBM+=|#<-bwlIauT&P)2`8 43bT^8$K}1`io6^@Hdn53J R' );
define( 'SECURE_AUTH_KEY',  'crZ%=eoQamL]ju$?$GMDI<e`;Ghx*sNlY5wu,@dzrf9JY-;6FIC3lzBvcN>RvPJN' );
define( 'LOGGED_IN_KEY',    '/k`os)!md-}Y U*C_L/i] X-ULtmfmq3#A8s$agp)q^Fl6~0B*BO[%(jq+><];Rd' );
define( 'NONCE_KEY',        ';B#E)o0#KqFpDa$;6$x7d]Xz><!=KOO-5j0<Ew7IRFz5D,0yO8H=aY>+:Bp!).Nr' );
define( 'AUTH_SALT',        'd3FvV:MX)?Yh27s3k0nz0*6$|#3QP:m$tkD`FbMSrlSF3Q84R|r)nUN5NqYE{!YA' );
define( 'SECURE_AUTH_SALT', '-OY,D{|ESw-lR.<Bk )/&H1arh>R/&C`%=zB*I3SOS`Kfp&:hJz/:t8}KM@*ua-x' );
define( 'LOGGED_IN_SALT',   'FZIQ[E^w+aSx KL9,hG,XCxYg#r^>G8[NXq7A|F=)s>xW-,9mh:1## ]Y%Nc:::s' );
define( 'NONCE_SALT',       'es#A{|(uPHp]0r7 o(U %~{S%sQ/l|twD:8KL}dab#1(jVB1].}RLr,D27>B9P>;' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
