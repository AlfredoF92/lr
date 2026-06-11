<?php
require dirname( __DIR__ ) . '/wp-load.php';

$id = 1463;
$el = get_post_meta( $id, '_elementor_data', true );
echo "Page 1463 elementor has llm_home_redirect: " . ( strpos( $el, 'llm_home_redirect' ) !== false ? 'YES' : 'NO' ) . "\n";
echo "Page 1463 elementor has llm_guest_home_redirect: " . ( strpos( $el, 'llm_guest_home_redirect' ) !== false ? 'YES' : 'NO' ) . "\n";

// Check footer 2861
$footer = get_post_meta( 2861, '_elementor_data', true );
echo "Footer 2861 has llm_guest_home_redirect: " . ( strpos( $footer, 'llm_guest_home_redirect' ) !== false ? 'YES' : 'NO' ) . "\n";
echo "Footer 2861 has llm_home_redirect: " . ( strpos( $footer, 'llm_home_redirect' ) !== false ? 'YES' : 'NO' ) . "\n";

// Where are inherit templates used?
foreach ( array( 2875, 2935, 2936 ) as $tid ) {
	$p = get_post( $tid );
	echo "\nTemplate $tid: {$p->post_title} ({$p->post_status})\n";
	echo "post_parent: {$p->post_parent}\n";
	$type = get_post_meta( $tid, '_elementor_template_type', true );
	echo "_elementor_template_type: $type\n";
}

// Check if single page template 2621 has home redirect
$single = get_post_meta( 2621, '_elementor_data', true );
echo "\nSingle template 2621 has llm_home_redirect: " . ( strpos( $single, 'llm_home_redirect' ) !== false ? 'YES' : 'NO' ) . "\n";

// Check pages 2870 and 2919 elementor
foreach ( array( 2870, 2919 ) as $pid ) {
	$data = get_post_meta( $pid, '_elementor_data', true );
	echo "Page $pid elementor llm_home_redirect: " . ( strpos( $data, 'llm_home_redirect' ) !== false ? 'YES' : 'NO' ) . "\n";
}

// Logo in header 2624?
$header = get_post_meta( 2624, '_elementor_data', true );
if ( preg_match( '/"url"\s*:\s*"([^"]+)"/', $header, $m ) ) {
	// too many matches - just search for home link
}
echo "\nHeader contains home_url or site logo link patterns:\n";
if ( preg_match_all( '/"url"\s*:\s*"([^"]*(?:home|\/|loverewrite)[^"]*)"/i', $header, $matches ) ) {
	foreach ( array_unique( $matches[1] ) as $url ) {
		echo "  $url\n";
	}
}
