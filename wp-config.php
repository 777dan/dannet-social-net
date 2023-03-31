<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'gbua_dannet' );

/** Database username */
define( 'DB_USER', 'gbua_dannet' );

/** Database password */
define( 'DB_PASSWORD', '76aba56c9ghj' );

/** Database hostname */
define( 'DB_HOST', 'mysql314.1gb.ua' );

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
define( 'AUTH_KEY',         '6.>p#d3kE}cYpYy]!Ii3lxS*3OK5ywCvb?E,v$bR,_?4c+iakl7<^O+Vv^m FAe^' );
define( 'SECURE_AUTH_KEY',  'PU|Q:zu[bY0Z98B!*q6C 7MQs5R@$0FF ^eOuHF:`).amN#@7Ub0%jYLr.(&q01o' );
define( 'LOGGED_IN_KEY',    '2F]EzFNVbxR9>j1k]0?c{cb=UWyV!Xic1/3$0+#5lp8PJrE6DHM3oG)B^d*HE:AR' );
define( 'NONCE_KEY',        'LxjaQ/vb4|x,dPBBmjK=Y2<<M<clt,lMh`pBP%}.pi]P&ZeLvPLio]Z5)$m RUP9' );
define( 'AUTH_SALT',        'i#P6Zl&?iw|oP6YGk=rFY70H3.oh.xJ{4*#1Lv(YVlgx+rnT8$e7_RU@ts2TzP]y' );
define( 'SECURE_AUTH_SALT', 'DBzi718;*Q>+3!S)e)#:~91h%]gly_Vgzkz51_EBk~#`t#;DB&D1TB/:`zc8o4Pw' );
define( 'LOGGED_IN_SALT',   '@T&%|auue&+8|a4o4K}YZ,!7+gk%L[@vRZ8BlrjeCA,Y2<C`n~mf=Jdzm-yR&m4Y' );
define( 'NONCE_SALT',       '{4[;~_2$P,-DD{Yb7kJ@GUZj)ZdEVM`lM7T6zQ%[{j{lN<}T&e`c6J|J6A1mH6.l' );

/**#@-*/

/**
 * WordPress database table prefix.
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
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
