<?php
/**
 * Shortcode header: coin, frasi completate, bravi ricevuti, lingua di studio.
 *
 * @package LLM_Tabelle
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LLM_User_Stat_Shortcodes
 */
class LLM_User_Stat_Shortcodes {

	const COINS    = 'llm_user_coins';
	const PHRASES  = 'llm_user_phrases';
	const BRAVI    = 'llm_user_bravi';
	const LANG     = 'llm_user_learning_lang';

	/**
	 * Avvio hook.
	 */
	public static function init() {
		add_shortcode( self::COINS, array( __CLASS__, 'render_coins' ) );
		add_shortcode( self::PHRASES, array( __CLASS__, 'render_phrases' ) );
		add_shortcode( self::BRAVI, array( __CLASS__, 'render_bravi' ) );
		add_shortcode( self::LANG, array( __CLASS__, 'render_learning_lang' ) );
	}

	/**
	 * Carica CSS condiviso con [llm_header_user].
	 */
	private static function enqueue_style() {
		wp_enqueue_style(
			'llm-header-user',
			LLM_TABELLE_URL . 'assets/llm-header-user.css',
			array(),
			LLM_TABELLE_VERSION
		);
	}

	/**
	 * @param string $path Path.
	 * @return string
	 */
	private static function normalize_path( $path ) {
		$path = trim( $path );
		if ( $path === '' ) {
			return '/';
		}
		if ( $path[0] !== '/' ) {
			return '/' . $path;
		}
		return $path;
	}

	/**
	 * URL per visitatori non loggati (default: home).
	 *
	 * @param array<string, string> $atts Attributi shortcode.
	 * @return string
	 */
	private static function guest_url( $atts ) {
		$path = self::normalize_path( isset( $atts['guest_path'] ) ? (string) $atts['guest_path'] : '/' );
		return esc_url( home_url( $path ) );
	}

	/**
	 * @param string               $target_url URL destinazione (coin/frasi/bravi).
	 * @param array<string, string> $atts Attributi shortcode.
	 * @param int                  $value Valore numerico.
	 * @param string               $label Etichetta con due punti (es. "Coin:" / "Bravi ricevuti:"), tradotta.
	 * @param string               $icon_svg Markup SVG da LLM_Header_UI_Icons.
	 * @return string
	 */
	private static function render_number_chip( $target_url, $atts, $value, $label, $icon_svg ) {
		if ( ! is_user_logged_in() ) {
			$n        = 0;
			$label    = trim( (string) $label );
			$aria_txt = trim( $label . ' ' . (string) $n );
			return sprintf(
				'<span class="llm-stat-chip-wrap"><a class="llm-stat-chip llm-stat-chip--guest llm-stat-chip--kv" href="%1$s" aria-label="%2$s"><span class="llm-stat-chip__icon">%3$s</span><span class="llm-stat-chip__body"><span class="llm-stat-chip__label">%4$s</span><span class="llm-stat-chip__value">%5$d</span></span></a></span>',
				self::guest_url( $atts ),
				esc_attr( $aria_txt ),
				$icon_svg, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statico.
				esc_html( $label ),
				$n
			);
		}

		$n        = max( 0, (int) $value );
		$label    = trim( (string) $label );
		$aria_txt = trim( $label . ' ' . (string) $n );

		return sprintf(
			'<span class="llm-stat-chip-wrap"><a class="llm-stat-chip llm-stat-chip--kv" href="%1$s" aria-label="%2$s"><span class="llm-stat-chip__icon">%3$s</span><span class="llm-stat-chip__body"><span class="llm-stat-chip__label">%4$s</span><span class="llm-stat-chip__value">%5$d</span></span></a></span>',
			esc_url( $target_url ),
			esc_attr( $aria_txt ),
			$icon_svg, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statico.
			esc_html( $label ),
			$n
		);
	}

	/**
	 * @param array<string, string>|string $atts Attributi.
	 * @return string
	 */
	public static function render_coins( $atts ) {
		self::enqueue_style();
		$atts = shortcode_atts(
			array(
				'coin_path'  => '/coin',
				'guest_path' => '/',
			),
			$atts,
			self::COINS
		);
		$target = esc_url( home_url( self::normalize_path( (string) $atts['coin_path'] ) ) );
		$uid    = get_current_user_id();
		$bal = $uid ? LLM_User_Stats::get_balance( $uid ) : 0;

		return self::render_number_chip( $target, $atts, $bal, 'Points:', LLM_Header_UI_Icons::coin() );
	}

	/**
	 * @param array<string, string>|string $atts Attributi.
	 * @return string
	 */
	public static function render_phrases( $atts ) {
		self::enqueue_style();
		$atts = shortcode_atts(
			array(
				'phrases_path' => '/frasi',
				'guest_path'   => '/',
			),
			$atts,
			self::PHRASES
		);
		$target = esc_url( home_url( self::normalize_path( (string) $atts['phrases_path'] ) ) );
		$uid    = get_current_user_id();
		$n = $uid ? LLM_User_Stats::count_completed_phrases( $uid ) : 0;

		return self::render_number_chip( $target, $atts, $n, 'Frasi completate:', LLM_Header_UI_Icons::phrases() );
	}

	/**
	 * @param array<string, string>|string $atts Attributi.
	 * @return string
	 */
	public static function render_bravi( $atts ) {
		self::enqueue_style();
		$atts = shortcode_atts(
			array(
				'bravi_path' => '/bravi',
				'guest_path' => '/',
			),
			$atts,
			self::BRAVI
		);
		$target = esc_url( home_url( self::normalize_path( (string) $atts['bravi_path'] ) ) );
		$uid    = get_current_user_id();
		$n = $uid ? LLM_Community::count_bravi_received( $uid ) : 0;

		return self::render_number_chip( $target, $atts, $n, 'Likes:', LLM_Header_UI_Icons::bravo() );
	}

	/**
	 * @param array<string, string>|string $atts Attributi.
	 * @return string
	 */
	public static function render_learning_lang( $atts ) {
		self::enqueue_style();
		$atts = shortcode_atts(
			array(),
			$atts,
			self::LANG
		);

		$icon = LLM_Header_UI_Icons::library();

		if ( ! is_user_logged_in() ) {
			// Legge la coppia dai cookie impostati da [llm_lang_cards].
			$known    = sanitize_key( wp_unslash( $_COOKIE['llm_interface_lang'] ?? '' ) );
			$learning = sanitize_key( wp_unslash( $_COOKIE['llm_learning_lang'] ?? '' ) );
			$chip_label = self::stories_in_label( $known );

			$known_valid    = LLM_Languages::is_valid( $known );
			$learning_valid = LLM_Languages::is_valid( $learning );

			// Determina testo da mostrare e URL destinazione.
			if ( $known_valid && $learning_valid ) {
				$display_label = strtoupper( $known ) . ' → ' . LLM_Languages::label( $learning );
				$pair_url      = self::pair_url_for( $known, $learning );
			} else {
				$display_label = '—';
				$pair_url      = '';
			}

			$dest      = '' !== $pair_url ? $pair_url : home_url( '/' );
			$aria_full = trim( $chip_label . ' ' . $display_label );
			$inner     = sprintf(
				'<span class="llm-stat-chip__body"><span class="llm-stat-chip__label">%1$s</span><span class="llm-stat-chip__value">%2$s</span></span>',
				esc_html( $chip_label ),
				esc_html( $display_label )
			);

			return sprintf(
				'<span class="llm-stat-chip-wrap"><a class="llm-stat-chip llm-stat-chip--guest llm-stat-chip--lang llm-stat-chip--kv" href="%1$s" aria-label="%2$s"><span class="llm-stat-chip__icon">%3$s</span>%4$s</a></span>',
				esc_url( $dest ),
				esc_attr( $aria_full ),
				$icon, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statico.
				$inner
			);
		}

		// Utente loggato.
		$uid            = get_current_user_id();
		$learning_code  = sanitize_key( (string) get_user_meta( $uid, LLM_User_Meta::LEARNING_LANG, true ) );
		$interface_code = sanitize_key( (string) get_user_meta( $uid, LLM_User_Meta::INTERFACE_LANG, true ) );
		$chip_label     = self::stories_in_label( $interface_code );
		$learning_valid = ( '' !== $learning_code && LLM_Languages::is_valid( $learning_code ) );
		$known_valid    = ( '' !== $interface_code && LLM_Languages::is_valid( $interface_code ) );

		if ( $learning_valid && $known_valid ) {
			$ui_lang       = class_exists( 'LLM_User_Settings_I18n' ) ? LLM_User_Settings_I18n::lang_for_user( $uid ) : '';
			$learning_name = class_exists( 'LLM_User_Settings_I18n' )
				? LLM_User_Settings_I18n::language_label( $learning_code, $ui_lang )
				: LLM_Languages::label( $learning_code );
			$display_label = strtoupper( $interface_code ) . ' → ' . $learning_name;
			$pair_url      = self::pair_url_for( $interface_code, $learning_code );
		} elseif ( $learning_valid ) {
			$display_label = LLM_Languages::label( $learning_code );
			$pair_url      = '';
		} elseif ( $known_valid ) {
			$display_label = strtoupper( $interface_code );
			$pair_url      = '';
		} else {
			$display_label = __( 'Lingua non impostata', 'llm-con-tabelle' );
			$pair_url      = '';
		}

		$dest      = '' !== $pair_url ? $pair_url : home_url( '/' );
		$aria_full = trim( $chip_label . ' ' . $display_label );
		$inner     = sprintf(
			'<span class="llm-stat-chip__body"><span class="llm-stat-chip__label">%1$s</span><span class="llm-stat-chip__value">%2$s</span></span>',
			esc_html( $chip_label ),
			esc_html( $display_label )
		);

		return sprintf(
			'<span class="llm-stat-chip-wrap"><a class="llm-stat-chip llm-stat-chip--lang llm-stat-chip--kv" href="%1$s" aria-label="%2$s"><span class="llm-stat-chip__icon">%3$s</span>%4$s</a></span>',
			esc_url( $dest ),
			esc_attr( $aria_full ),
			$icon, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statico.
			$inner
		);
	}

	/**
	 * Traduce "Storie in:" nella lingua conosciuta dall'utente.
	 *
	 * @param string $known Codice lingua conosciuta (es. 'it', 'en', 'pl', 'es').
	 * @return string
	 */
	private static function stories_in_label( $known ) {
		$labels = array(
			'it' => 'Storie in:',
			'en' => 'Stories in:',
			'pl' => 'Historie w:',
			'es' => 'Historias en:',
		);
		return $labels[ $known ] ?? 'Storie in:';
	}

	/**
	 * Restituisce il permalink della pagina per la coppia linguistica, '' se non configurata.
	 *
	 * @param string $known    Codice lingua conosciuta.
	 * @param string $learning Codice lingua da imparare.
	 * @return string
	 */
	private static function pair_url_for( $known, $learning ) {
		if ( ! class_exists( 'LLM_Home_Redirect' ) ) {
			return '';
		}
		return LLM_Home_Redirect::pair_url( $known, $learning );
	}
}
