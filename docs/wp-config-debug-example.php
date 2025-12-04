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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 's1149026_gusto' );

/** MySQL database username */
define( 'DB_USER', 's1149026_gusto' );

/** MySQL database password */
define( 'DB_PASSWORD', 'makovilia' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '>R3.]qgZ91tpsP&R1=Sr:@0!wtI[/g=.qqO)Ncd5;Xt_@vxc-NV(~5xk)tPh52=u' );
define( 'SECURE_AUTH_KEY',   '4f#/~R85KC-|lq)K1~f_:{x&w.W</OqJV UFhY7@z!ed6`J:<V}c#)y>:qL`~6m/' );
define( 'LOGGED_IN_KEY',     'GQ<Xb4|WCb+~jL8;oln%%<S2Y,^[bgI>Gld|0tRNMlT)FYXS#N`&YiNf=R $=>15' );
define( 'NONCE_KEY',         'a`/bj^5MDrRDGx}z#BgL2+a)9` T&hDsliNldgjb]qoXP?9ielnm~3y0urS48khK' );
define( 'AUTH_SALT',         'G*]7^Qqn?Fj(eLu{J-kP(BFC_-}K#&|dOIC$9rd$<HSnY,MCnhph?V_/1Kf[#kfZ' );
define( 'SECURE_AUTH_SALT',  'h2A-J)0o+fGy5,8}]z^.}:N.Pv/g+2U[zrWDAVL*sS_>wyb2!S+86CVPaL&h:lg.' );
define( 'LOGGED_IN_SALT',    'XSH`5d?JG2Q~Om^-/$sBw2Sb%RyuVg_ c,EZqYeo5/PA!,)RWcWuiiS/_:rN]/Sm' );
define( 'NONCE_SALT',        'IYV.imgcWsmT&0).!0UG_{BcM%)SM],;i8:sep8QWeN?X&yNwTSa~n}@E.JeT)ZJ' );
define( 'WP_CACHE_KEY_SALT', '%*6[?4J@PC7rn@&S|n_fPdho~f[zFYSW,C~j?*S3<N<.f(_]H(;nrWbAH.J;473u' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'dp___bk_250951_wp_';

define('WP_HOME', 'https://gustolocal.es');
define('WP_SITEURL', 'https://gustolocal.es');

// ============================================
// НАСТРОЙКИ ПАМЯТИ (у вас уже есть)
// ============================================
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_MAX_MEMORY_LIMIT', '512M' );

// ============================================
// НАСТРОЙКИ ОТЛАДКИ И ЛОГИРОВАНИЯ
// ДОБАВЬТЕ ЭТИ СТРОЧКИ ЗДЕСЬ (перед комментарием ниже)
// ============================================
// Включить отладку и логирование ошибок
define('WP_DEBUG', true);              // Включить режим отладки
define('WP_DEBUG_LOG', true);          // Логировать ошибки в файл wp-content/debug.log
define('WP_DEBUG_DISPLAY', false);     // НЕ показывать ошибки на сайте (только в логах)
@ini_set('display_errors', 0);         // Отключить вывод ошибок на экран

// Дополнительные настройки отладки (опционально)
define('SCRIPT_DEBUG', true);          // Использовать не минифицированные версии JS/CSS
define('SAVEQUERIES', true);           // Сохранять все SQL запросы (для отладки производительности)

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';




