<?php
require dirname( __DIR__ ) . '/wp-load.php';

foreach ( array( 2919, 2870 ) as $id ) {
	$data = get_post_meta( $id, '_elementor_data', true );
	echo "=== Page $id ===\n";
	if ( preg_match_all( '/\[llm_[^\]]+\]/', $data, $m ) ) {
		echo 'Shortcodes: ' . implode( ', ', array_unique( $m[0] ) ) . "\n";
	} else {
		echo "No shortcodes in elementor\n";
	}
	// widget types
	if ( preg_match_all( '/"widgetType"\s*:\s*"([^"]+)"/', $data, $w ) ) {
		echo 'Widgets: ' . implode( ', ', array_unique( $w[1] ) ) . "\n";
	}
	echo "\n";
}
