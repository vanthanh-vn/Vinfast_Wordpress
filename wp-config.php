<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'pmm' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '1Phancuagiadinh@' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'a~6edZitR&T@ZvW/X{YvEy:KHORhhAz,Cj:DY&5Ss;<SQbye(PzE-vIW,I+h$T-Z' );
define( 'SECURE_AUTH_KEY',  'h*(S7=mb+P{P|FR7:B6Uc:BqPCI0sA[AorLe8>^:|IBF.?rN/0ISS;*n|<owLob$' );
define( 'LOGGED_IN_KEY',    'PBbg4]Mn0iX pU>Ztg|.*}|fU7jclc0Orun])$cG5 g`w?`,P5i M|HwjXTd6w7q' );
define( 'NONCE_KEY',        'p%/Rbe)@Pq1E;FGfy_@}fKBc4#n~&~1T^M=g+gR)?C,[[YtCBsND<f)Ug4Kh1o]h' );
define( 'AUTH_SALT',        'n3qP1WA1aG8x}K)V+.WSn^$<=X+K+>.uXc$b;_n8Bq-d:bQ!BBo8H-50VA%HAPfc' );
define( 'SECURE_AUTH_SALT', 'Ld/Awt12!,I[yF)eE(V4#ao(O}qgm=0r0g8&GNSa~-c4r:hf$7Q].jDiPR?%hB^+' );
define( 'LOGGED_IN_SALT',   '4<V]=+-&H.bB@!QUUxJ8SX41(&>C2UQUu%jWvWrkw7&90lAYJG!&-<Fw7@0VuC{e' );
define( 'NONCE_SALT',       '.IzUFXI|d=VDS8V&y(2!y2[B/_U3iHWR[m^(1wsXcvhvV_qUgoB?t5UXrLD?O-3Q' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

/**
 * Cloudflare Tunnel support for the public VinFast domain.
 *
 * Local access stays unchanged at http://localhost/vinfast/.
 * Public access uses the current Cloudflare request scheme so HTTP still
 * works while Universal SSL is pending.
 */
$vf_is_https =
	( isset( $_SERVER['HTTPS'] ) && 'off' !== strtolower( (string) $_SERVER['HTTPS'] ) )
	|| ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === strtolower( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) )
	|| ( isset( $_SERVER['HTTP_CF_VISITOR'] ) && false !== strpos( $_SERVER['HTTP_CF_VISITOR'], '"scheme":"https"' ) );

if (
	$vf_is_https
) {
	$_SERVER['HTTPS'] = 'on';
}

if ( isset( $_SERVER['HTTP_HOST'] ) ) {
	$vf_public_host = strtolower( $_SERVER['HTTP_HOST'] );
	if ( in_array( $vf_public_host, array( 'vinfasttpc.io.vn', 'www.vinfasttpc.io.vn' ), true ) ) {
		$vf_public_scheme = $vf_is_https ? 'https' : 'http';
		define( 'WP_HOME', $vf_public_scheme . '://' . $vf_public_host . '/vinfast' );
		define( 'WP_SITEURL', $vf_public_scheme . '://' . $vf_public_host . '/vinfast' );
	}
}


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
