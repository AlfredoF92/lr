<?php
require dirname( __DIR__ ) . '/wp-load.php';

echo "=== llm_home_redirect_pairs ===\n";
print_r( get_option( 'llm_home_redirect_pairs', array() ) );

echo "\n=== guest redirect flag ===\n";
echo get_option( 'llm_guest_home_redirect_active', '(not set)' ) . "\n";

echo "\n=== show_on_front / page_on_front ===\n";
echo 'show_on_front: ' . get_option( 'show_on_front' ) . "\n";
echo 'page_on_front: ' . get_option( 'page_on_front' ) . "\n";
echo 'home_url: ' . home_url( '/' ) . "\n";

echo "\n=== Pages/posts with redirect shortcodes in post_content ===\n";
$posts = get_posts(
	array(
		'post_type'      => array( 'page', 'post', 'elementor_library' ),
		'posts_per_page' => -1,
		'post_status'    => 'any',
	)
);
foreach ( $posts as $p ) {
	$c = $p->post_content;
	if ( false !== strpos( $c, 'llm_home_redirect' ) || false !== strpos( $c, 'llm_guest_home_redirect' ) ) {
		echo $p->ID . ' | ' . $p->post_type . ' | ' . $p->post_title . ' | ' . get_permalink( $p->ID ) . "\n";
	}
}

echo "\n=== Elementor meta search ===\n";
global $wpdb;
$rows = $wpdb->get_results(
	"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data' AND (meta_value LIKE '%llm_home_redirect%' OR meta_value LIKE '%llm_guest_home_redirect%') LIMIT 20"
);
foreach ( $rows as $row ) {
	$p = get_post( (int) $row->post_id );
	$title = $p ? $p->post_title : '?';
	echo $row->post_id . ' | ' . $title . ' | ' . get_permalink( (int) $row->post_id ) . "\n";
	if ( false !== strpos( $row->meta_value, 'llm_home_redirect' ) ) {
		echo "  -> contains llm_home_redirect\n";
	}
	if ( false !== strpos( $row->meta_value, 'llm_guest_home_redirect' ) ) {
		echo "  -> contains llm_guest_home_redirect\n";
	}
}
