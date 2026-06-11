<?php
require dirname( __DIR__ ) . '/wp-load.php';
$d = get_post_meta( 2624, '_elementor_data', true );
echo ( strpos( $d, 'localhost' ) !== false ? 'still has localhost' : 'ok no localhost' ) . "\n";
preg_match_all( '/"url":"([^"]+)"/', $d, $m );
foreach ( array_unique( $m[1] ) as $u ) {
	echo $u . "\n";
}
