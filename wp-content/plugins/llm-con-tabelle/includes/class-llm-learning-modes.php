<?php
/**
 * Registro delle modalità di apprendimento del gioco frasi.
 *
 * Nuove modalità si aggiungono in self::registry() oppure via filtro `llm_learning_modes`.
 *
 * @package LLM_Tabelle
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LLM_Learning_Modes {

	const USER_META    = '_llm_learning_mode';
	const STORAGE_KEY  = 'llm_learning_mode';
	const AJAX_ACTION  = 'llm_learning_mode_save';
	const NONCE_ACTION = 'llm_learning_mode';

	/** Modalità storica a due fasi. */
	const MODE_LOVEREWRITE = 'loverewrite';

	/** Fase unica: traduzione corretta e avanti. */
	const MODE_RESOLVE_GO = 'resolve_go';

	/** Fase unica senza controlli: si avanza sempre. */
	const MODE_READ_GO_FAST = 'read_go_fast';

	public static function init() {
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'ajax_save' ) );
	}

	/**
	 * Modalità disponibili, in ordine di presentazione.
	 *
	 * @return array<int, array{id: string, label: string, description: string}>
	 */
	public static function all() {
		$modes = array(
			array(
				'id'          => self::MODE_LOVEREWRITE,
				'label'       => LLM_Phrase_Game_I18n::get( 'mode_loverewrite_label' ),
				'description' => LLM_Phrase_Game_I18n::get( 'mode_loverewrite_desc' ),
			),
			array(
				'id'          => self::MODE_RESOLVE_GO,
				'label'       => LLM_Phrase_Game_I18n::get( 'mode_resolve_go_label' ),
				'description' => LLM_Phrase_Game_I18n::get( 'mode_resolve_go_desc' ),
			),
			array(
				'id'          => self::MODE_READ_GO_FAST,
				'label'       => LLM_Phrase_Game_I18n::get( 'mode_read_go_fast_label' ),
				'description' => LLM_Phrase_Game_I18n::get( 'mode_read_go_fast_desc' ),
			),
		);

		/**
		 * Permette di registrare nuove modalità di apprendimento.
		 *
		 * @param array<int, array{id: string, label: string, description: string}> $modes
		 */
		$modes = (array) apply_filters( 'llm_learning_modes', $modes );

		$out = array();
		foreach ( $modes as $mode ) {
			if ( ! is_array( $mode ) || empty( $mode['id'] ) ) {
				continue;
			}
			$out[] = array(
				'id'          => sanitize_key( (string) $mode['id'] ),
				'label'       => isset( $mode['label'] ) ? (string) $mode['label'] : (string) $mode['id'],
				'description' => isset( $mode['description'] ) ? (string) $mode['description'] : '',
			);
		}

		return $out;
	}

	/**
	 * Identificatore della modalità predefinita.
	 *
	 * @return string
	 */
	public static function default_mode() {
		return (string) apply_filters( 'llm_learning_mode_default', self::MODE_LOVEREWRITE );
	}

	/**
	 * @param string $mode_id
	 * @return bool
	 */
	public static function is_valid( $mode_id ) {
		$mode_id = sanitize_key( (string) $mode_id );
		if ( '' === $mode_id ) {
			return false;
		}
		foreach ( self::all() as $mode ) {
			if ( $mode['id'] === $mode_id ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Modalità salvata per l'utente (default se assente o non più valida).
	 *
	 * @param int $user_id
	 * @return string
	 */
	public static function get_for_user( $user_id ) {
		$saved = (string) get_user_meta( (int) $user_id, self::USER_META, true );
		return self::is_valid( $saved ) ? sanitize_key( $saved ) : self::default_mode();
	}

	/**
	 * @param int    $user_id
	 * @param string $mode_id
	 * @return bool True se salvata.
	 */
	public static function set_for_user( $user_id, $mode_id ) {
		if ( ! self::is_valid( $mode_id ) ) {
			return false;
		}
		update_user_meta( (int) $user_id, self::USER_META, sanitize_key( (string) $mode_id ) );
		return true;
	}

	/**
	 * Modalità corrente: dal profilo se loggato, altrimenti default (gli ospiti usano localStorage lato JS).
	 *
	 * @return string
	 */
	public static function current() {
		if ( is_user_logged_in() ) {
			return self::get_for_user( get_current_user_id() );
		}
		return self::default_mode();
	}

	/**
	 * Modalità da applicare a una richiesta AJAX.
	 *
	 * Per gli utenti loggati vale sempre il profilo: è l'unico dato di cui il server
	 * si fida, visto che da qui dipendono punti e avanzamento. Gli ospiti tengono la
	 * scelta in localStorage, quindi la modalità arriva dal client e va solo validata.
	 *
	 * @param string $posted_mode Modalità dichiarata dal client.
	 * @return string
	 */
	public static function for_request( $posted_mode = '' ) {
		if ( is_user_logged_in() ) {
			return self::get_for_user( get_current_user_id() );
		}
		$posted_mode = sanitize_key( (string) $posted_mode );
		return self::is_valid( $posted_mode ) ? $posted_mode : self::default_mode();
	}

	/**
	 * Se la modalità avanza senza controllare il testo scritto dall'utente.
	 *
	 * @param string $mode_id
	 * @return bool
	 */
	public static function skips_validation( $mode_id ) {
		$skips = self::MODE_READ_GO_FAST === sanitize_key( (string) $mode_id );

		/**
		 * Permette a modalità registrate da terzi di saltare la validazione.
		 *
		 * @param bool   $skips
		 * @param string $mode_id
		 */
		return (bool) apply_filters( 'llm_learning_mode_skips_validation', $skips, $mode_id );
	}

	/**
	 * Etichetta leggibile di una modalità.
	 *
	 * @param string $mode_id
	 * @return string
	 */
	public static function label( $mode_id ) {
		$mode_id = sanitize_key( (string) $mode_id );
		foreach ( self::all() as $mode ) {
			if ( $mode['id'] === $mode_id ) {
				return $mode['label'];
			}
		}
		return '';
	}

	/**
	 * Dati per wp_localize_script.
	 *
	 * @return array<string, mixed>
	 */
	public static function script_data() {
		return array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( self::NONCE_ACTION ),
			'action'      => self::AJAX_ACTION,
			'isLoggedIn'  => is_user_logged_in(),
			'storageKey'  => self::STORAGE_KEY,
			'current'     => self::current(),
			'defaultMode' => self::default_mode(),
			'modes'       => self::all(),
			'savedMsg'    => LLM_Phrase_Game_I18n::get( 'learning_mode_saved' ),
			'errorMsg'    => LLM_Phrase_Game_I18n::get( 'learning_mode_error' ),
		);
	}

	/**
	 * AJAX: salva la modalità scelta dall'utente loggato.
	 */
	public static function ajax_save() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => LLM_Phrase_Game_I18n::get( 'learning_mode_error' ) ), 403 );
		}

		$mode_id = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : '';
		if ( ! self::set_for_user( get_current_user_id(), $mode_id ) ) {
			wp_send_json_error( array( 'message' => LLM_Phrase_Game_I18n::get( 'learning_mode_error' ) ), 400 );
		}

		wp_send_json_success(
			array(
				'mode'  => $mode_id,
				'label' => self::label( $mode_id ),
			)
		);
	}
}
