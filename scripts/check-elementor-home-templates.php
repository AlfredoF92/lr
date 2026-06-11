<?php
require dirname( __DIR__ ) . '/wp-load.php';

$ids = array( 1463, 2870, 2919 );

foreach ( $ids as $id ) {
	$p = get_post( $id );
	echo "=== $id {$p->post_title} ===\n";
	echo 'URL: ' . get_permalink( $id ) . "\n";
	echo '_wp_page_template: ' . get_post_meta( $id, '_wp_page_template', true ) . "\n";
	echo '_elementor_template_type: ' . get_post_meta( $id, '_elementor_template_type', true ) . "\n";
	echo '_elementor_edit_mode: ' . get_post_meta( $id, '_elementor_edit_mode', true ) . "\n";

	$el = get_post_meta( $id, '_elementor_data', true );
	if ( is_string( $el ) && $el !== '' ) {
		echo "Has own _elementor_data: yes (" . strlen( $el ) . " bytes)\n";
		if ( strpos( $el, 'llm_home_redirect' ) !== false ) {
			echo "  -> contains llm_home_redirect in elementor data\n";
		}
	} else {
		echo "Has own _elementor_data: no\n";
	}
	echo "\n";
}

// Check Elementor theme builder conditions for Home templates
echo "=== Elementor conditions (if any in options) ===\n";
$conditions = get_option( 'elementor_pro_theme_builder_conditions', array() );
if ( ! empty( $conditions ) ) {
	print_r( $conditions );
}

// Search all elementor templates named Home with shortcode
echo "\n=== All published pages with elementor llm_home_redirect ===\n";
global $wpdb;
$rows = $wpdb->get_results(
	"SELECT p.ID, p.post_title, p.post_status, pm.meta_value FROM {$wpdb->posts} p
	INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_elementor_data'
	WHERE pm.meta_value LIKE '%llm_home_redirect%' AND p.post_status = 'publish'"
);
foreach ( $rows as $row ) {
	echo $row->ID . ' | ' . $row->post_title . ' | ' . get_permalink( (int) $row->ID ) . "\n";
}
