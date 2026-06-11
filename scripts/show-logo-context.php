<?php
require dirname( __DIR__ ) . '/wp-load.php';

$data = get_post_meta( 2624, '_elementor_data', true );
$pos = strpos( $data, 'localhost' );
if ( false !== $pos ) {
	echo substr( $data, max( 0, $pos - 200 ), 400 ) . "\n";
}
