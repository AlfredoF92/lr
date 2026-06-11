<?php
require dirname( __DIR__ ) . '/wp-load.php';

global $wpdb;
$pages = $wpdb->get_results(
	"SELECT ID, post_title, post_name, post_status FROM {$wpdb->posts}
	WHERE post_type = 'page' AND post_name IN ('login','logout','area-personale','home')
	ORDER BY post_name"
);
foreach ( $pages as $p ) {
	echo "{$p->ID} | {$p->post_name} | {$p->post_title} | {$p->post_status} | " . get_permalink( (int) $p->ID ) . "\n";
	$data = get_post_meta( (int) $p->ID, '_elementor_data', true );
	if ( is_string( $data ) && preg_match_all( '/\[llm_[^\]]+\]/', $data, $m ) ) {
		echo '  shortcodes: ' . implode( ', ', array_unique( $m[0] ) ) . "\n";
	}
}

echo "\nHome #1463 shortcodes in elementor:\n";
$data = get_post_meta( 1463, '_elementor_data', true );
preg_match_all( '/\[llm_[^\]]+\]/', $data, $m );
echo implode( ', ', array_unique( $m[0] ) ) . "\n";
