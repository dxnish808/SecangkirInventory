<?php
/*
|--------------------------------------------------------------------------
| OWSA-INV V2
|--------------------------------------------------------------------------
| Author: Siamon Hasan
| Project Name: OSWA-INV
| Version: v2
| Offcial page: http://oswapp.com/
| facebook Page: https://www.facebook.com/oswapp
|
|
|
*/

// Database configuration - supports both Docker and traditional setup
define( 'DB_HOST', getenv('DB_HOST') ?: 'localhost' );          // Set database host
define( 'DB_USER', getenv('DB_USER') ?: 'root' );             // Set database user
define( 'DB_PASS', getenv('DB_PASS') ?: '' );                 // Set database password
define( 'DB_NAME', getenv('DB_NAME') ?: 'inventory_system' );  // Set database name

?>
