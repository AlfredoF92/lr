<?php
/**
 * Corregge il link del logo nell'header Elementor (#2624):
 * sostituisce URL localhost/rewrite con la home del sito corrente.
 *
 * Uso: php scripts/fix-header-logo-url.php
 */
require dirname( __DIR__ ) . '/wp-load.php';

$post_id = 2624;
$data    = get_post_meta( $post_id, '_elementor_data', true );

if ( ! is_string( $data ) || '' === $data ) {
	echo "Nessun _elementor_data per post $post_id\n";
	exit( 1 );
}

$home = home_url( '/' );
$new  = str_replace( 'http://localhost/rewrite/', $home, $data );
$new  = str_replace( 'http:\/\/localhost\/rewrite\/', str_replace( '/', '\\/', $home ), $new );

if ( $new === $data ) {
	echo "Nessuna occorrenza di localhost/rewrite trovata.\n";
	exit( 0 );
}

update_post_meta( $post_id, '_elementor_data', wp_slash( $new ) );

if ( class_exists( '\Elementor\Plugin' ) ) {
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}

echo "Logo aggiornato a: $home\n";
