<?php
require dirname( __DIR__ ) . '/wp-load.php';

echo 'siteurl: ' . get_option( 'siteurl' ) . "\n";
echo 'home: ' . get_option( 'home' ) . "\n";
echo 'show_on_front: ' . get_option( 'show_on_front' ) . "\n";
echo 'page_on_front: ' . get_option( 'page_on_front' ) . "\n";
echo 'home_url(/): ' . home_url( '/' ) . "\n";
echo 'front page permalink: ' . get_permalink( get_option( 'page_on_front' ) ) . "\n";

$pairs = get_option( 'llm_home_redirect_pairs', array() );
echo "\nllm_home_redirect_pairs:\n";
print_r( $pairs );

echo "\nllm_guest_home_redirect_active: " . get_option( 'llm_guest_home_redirect_active' ) . "\n";

// Extract shortcode text from page 1463 elementor JSON
$data = get_post_meta( 1463, '_elementor_data', true );
if ( preg_match_all( '/\[llm_[^\]]+\]/', $data, $m ) ) {
	echo "\nShortcodes in 1463 elementor: " . implode( ', ', array_unique( $m[0] ) ) . "\n";
} else {
	echo "\nNo shortcodes in 1463 elementor JSON\n";
}

// Search ALL elementor data for llm_home_redirect on any post status
global $wpdb;
$all = $wpdb->get_results(
	"SELECT p.ID, p.post_title, p.post_status, p.post_type FROM {$wpdb->posts} p
	INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_elementor_data'
	WHERE pm.meta_value LIKE '%llm_home_redirect%'"
);
echo "\nAll posts with llm_home_redirect in elementor:\n";
foreach ( $all as $row ) {
	echo "  {$row->ID} | {$row->post_status} | {$row->post_type} | {$row->post_title} | " . get_permalink( (int) $row->ID ) . "\n";
}
