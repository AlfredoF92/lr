<?php
/**
 * Simula il comportamento di [llm_guest_home_redirect] su varie URL.
 */
require dirname( __DIR__ ) . '/wp-load.php';

echo "=== Config ===\n";
echo 'home_url(/): ' . home_url( '/' ) . "\n";
echo 'page_on_front: ' . get_option( 'page_on_front' ) . ' (' . get_the_title( (int) get_option( 'page_on_front' ) ) . ")\n";
echo 'llm_guest_home_redirect_active: ' . var_export( get_option( 'llm_guest_home_redirect_active' ), true ) . "\n";

$footer = get_post_meta( 2861, '_elementor_data', true );
echo 'Footer #2861 has shortcode: ' . ( strpos( $footer, 'llm_guest_home_redirect' ) !== false ? 'YES' : 'NO' ) . "\n\n";

$test_paths = array(
	'/',
	'/localloverewrite/',
	'/localloverewrite',
	'/it-polish/',
	'/pl-wloski/',
	'/home/',
	'/login/',
	'/area-personale/',
);

echo "=== is_current_page_home simulation (guest) ===\n";
echo "Note: is_front_page() depends on main query; path-only check shown separately.\n\n";

$home_path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
if ( ! is_string( $home_path ) || '' === $home_path ) {
	$home_path = '/';
}

foreach ( $test_paths as $path ) {
	$norm = untrailingslashit( $path ) ?: '/';
	$home_norm = untrailingslashit( $home_path ) ?: '/';
	$is_home_path = ( $norm === $home_norm );
	$would_redirect = ! $is_home_path ? 'YES -> ' . home_url( '/' ) : 'NO (stay)';
	echo sprintf( "%-25s path_match_home=%s  redirect=%s\n", $path, $is_home_path ? 'true' : 'false', $would_redirect );
}

echo "\n=== Live curl (guest, no cookie) ===\n";
$urls = array(
	home_url( '/' ),
	home_url( '/it-polish/' ),
	home_url( '/login/' ),
	home_url( '/home/' ),
);
foreach ( $urls as $url ) {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_NOBODY         => true,
		CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 10,
	) );
	curl_exec( $ch );
	$code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	$loc  = curl_getinfo( $ch, CURLINFO_REDIRECT_URL );
	curl_close( $ch );
	echo "$url\n  HTTP $code" . ( $loc ? " -> $loc" : '' ) . "\n";
}
