<?php
require dirname( __DIR__ ) . '/wp-load.php';

$ids = array( 1463, 2870, 2875, 2919, 2935, 2936 );

foreach ( $ids as $id ) {
	$p = get_post( $id );
	if ( ! $p ) {
		echo "$id: NOT FOUND\n";
		continue;
	}
	echo "=== ID $id: {$p->post_title} ({$p->post_status}) ===\n";
	echo 'URL: ' . get_permalink( $id ) . "\n";
	echo 'post_content shortcodes: ';
	if ( strpos( $p->post_content, 'llm_home_redirect' ) !== false ) {
		echo 'llm_home_redirect ';
	}
	if ( strpos( $p->post_content, 'llm_guest_home_redirect' ) !== false ) {
		echo 'llm_guest_home_redirect ';
	}
	echo "\n";

	$el = get_post_meta( $id, '_elementor_data', true );
	if ( is_string( $el ) ) {
		if ( strpos( $el, 'llm_home_redirect' ) !== false ) {
			echo "Elementor: has llm_home_redirect\n";
		}
		if ( strpos( $el, 'llm_guest_home_redirect' ) !== false ) {
			echo "Elementor: has llm_guest_home_redirect\n";
		}
	}
	echo "\n";
}

echo "=== Simulated logged-in redirect it+pl ===\n";
if ( class_exists( 'LLM_Home_Redirect' ) ) {
	echo LLM_Home_Redirect::pair_url( 'it', 'pl' ) . "\n";
}
echo "=== Simulated logged-in redirect pl+it ===\n";
echo LLM_Home_Redirect::pair_url( 'pl', 'it' ) . "\n";
