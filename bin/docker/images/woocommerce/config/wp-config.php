<?php
define('DB_NAME', 'wordpress');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
define('AUTH_KEY',         '_76kspvN9EA>|8ir qsG^v}-7M6lqB~GenbrooTDCORj>Qtv-`)|PdE9E?E?QYF+');
define('SECURE_AUTH_KEY',  '$)P8D6:Dv? AIJ@BHM#Lvd2UUD`;1/mbs06j39MgEgH[/a)&,Qe>PlLyz$as:AD?');
define('LOGGED_IN_KEY',    '`{Lh|cy^I?*5slU/n6-k,l88=Kn`B3m~y9@H^/IrgIBa@0-t~{$sw{3xed|R[ZmO');
define('NONCE_KEY',        'U(Vch9H~O1;8=y6S.C+YoBfc2M{A~DmQqnkF12)_XIgi;5B,acL=~$M@t@MduVI~');
define('AUTH_SALT',        ')H{2^V-ztc5yw+Ur>}8cM-3:%>ra,UwRpOw{qd:48bfkZcQl)JuM0oWq=_R7d;^F');
define('SECURE_AUTH_SALT', '+$si+*/gahJ!,Pl}>!=0&A1W0XHQ@f/iWq^Puv=j.FfObYEA|x9doD+0~OT,]`+?');
define('LOGGED_IN_SALT',   ':1/6(r:Ez9/till-z1B3U~PmTak`}x#}c~M_V=aE)1e{iOxAboC`6cmR`YVYxY:E');
define('NONCE_SALT',       'XE}uTRJ3[)e5c7*kic&m.}2l{r-D2l7WG%g&tn{-I+m:O{-;lB`g=TmI!vL@4RCC');
$table_prefix  = 'wp_';
define('WP_DEBUG', false);
if ( !defined('ABSPATH') )
    define('ABSPATH', dirname(__FILE__) . '/');
require_once(ABSPATH . 'wp-settings.php');