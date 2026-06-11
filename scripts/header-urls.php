<?php
require dirname( __DIR__ ) . '/wp-load.php';

$h = get_post_meta( 2624, '_elementor_data', true );
preg_match_all( '/"url"\s*:\s*"([^"]+)"/', $h, $m );
foreach ( array_unique( $m[1] ) as $u ) {
	echo $u . "\n";
}
