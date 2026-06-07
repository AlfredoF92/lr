<?php
/**
 * Shortcode [llm_lang_cards] — sezione "Se conosci X, impara:" con card per coppia linguistica.
 *
 * Funzionamento:
 * - Mostra un <select> con le lingue interfaccia + pulsante Conferma per cambiare la lingua conosciuta.
 * - Sotto, una griglia di card (una per ogni lingua imparabile != lingua conosciuta).
 * - Card con coppia configurata in llm_home_redirect_pairs → bottone con form POST che:
 *     1. Salva _llm_interface_lang (lingua conosciuta) + _llm_learning_lang (lingua target)
 *     2. Salva entrambe in cookie per 30 giorni (ospiti e loggati)
 *     3. Fa redirect alla pagina della coppia
 * - Card senza coppia configurata → badge "Coming Soon".
 *
 * @package LLM_Tabelle
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LLM_Lang_Cards_Shortcode {

	const SHORTCODE         = 'llm_lang_cards';
	const NONCE_ACTION_LANG = 'llm_lang_cards_save';
	const NONCE_FIELD_LANG  = 'llm_lang_cards_nonce';
	const POST_FLAG_LANG    = 'llm_lang_cards_submit';

	const NONCE_ACTION_CARD = 'llm_lang_cards_card';
	const NONCE_FIELD_CARD  = 'llm_lang_cards_card_nonce';
	const POST_FLAG_CARD    = 'llm_lang_cards_card_submit';

	const COOKIE_KNOWN    = 'llm_interface_lang';
	const COOKIE_LEARNING = 'llm_learning_lang';
	const COOKIE_DAYS     = 30;

	public static function init() {
		add_shortcode( self::SHORTCODE, array( __CLASS__, 'render' ) );
		add_action( 'init', array( __CLASS__, 'maybe_handle_form' ), 5 );
	}

	/* ------------------------------------------------------------------ */
	/* Form handler                                                         */
	/* ------------------------------------------------------------------ */

	public static function maybe_handle_form() {
		// Selettore lingua conosciuta.
		if ( ! empty( $_POST[ self::POST_FLAG_LANG ] ) ) {
			self::handle_known_lang_form();
			return;
		}

		// Bottone card (salva lingua conosciuta + lingua target → redirect alla pagina coppia).
		if ( ! empty( $_POST[ self::POST_FLAG_CARD ] ) ) {
			self::handle_card_form();
			return;
		}
	}

	/* ---- Handler: cambia lingua conosciuta ---- */
	private static function handle_known_lang_form() {
		if (
			! isset( $_POST[ self::NONCE_FIELD_LANG ] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD_LANG ] ) ),
				self::NONCE_ACTION_LANG
			)
		) {
			return;
		}

		$lang = sanitize_key( wp_unslash( $_POST['llm_known_lang'] ?? '' ) );
		if ( ! LLM_Languages::is_valid( $lang ) ) {
			return;
		}

		if ( is_user_logged_in() ) {
			update_user_meta( get_current_user_id(), LLM_User_Meta::INTERFACE_LANG, $lang );
		}

		self::set_cookie( self::COOKIE_KNOWN, $lang );

		$redirect = isset( $_POST['_wp_http_referer'] )
			? wp_validate_redirect( wp_unslash( $_POST['_wp_http_referer'] ), home_url( '/' ) )
			: home_url( '/' );

		wp_safe_redirect( $redirect );
		exit;
	}

	/* ---- Handler: click bottone card (salva coppia + redirect) ---- */
	private static function handle_card_form() {
		if (
			! isset( $_POST[ self::NONCE_FIELD_CARD ] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD_CARD ] ) ),
				self::NONCE_ACTION_CARD
			)
		) {
			return;
		}

		$known    = sanitize_key( wp_unslash( $_POST['llm_known_lang'] ?? '' ) );
		$learning = sanitize_key( wp_unslash( $_POST['llm_learning_lang'] ?? '' ) );
		$dest     = wp_unslash( $_POST['llm_card_redirect'] ?? '' );

		if ( ! LLM_Languages::is_valid( $known ) || ! LLM_Languages::is_valid( $learning ) ) {
			return;
		}

		// Salva per utente loggato.
		if ( is_user_logged_in() ) {
			$uid = get_current_user_id();
			update_user_meta( $uid, LLM_User_Meta::INTERFACE_LANG, $known );
			update_user_meta( $uid, LLM_User_Meta::LEARNING_LANG, $learning );
		}

		// Salva cookie (loggati e ospiti).
		self::set_cookie( self::COOKIE_KNOWN, $known );
		self::set_cookie( self::COOKIE_LEARNING, $learning );

		// Redirect alla pagina della coppia.
		$redirect = ( '' !== $dest )
			? wp_validate_redirect( $dest, home_url( '/' ) )
			: home_url( '/' );

		wp_safe_redirect( $redirect );
		exit;
	}

	/* ------------------------------------------------------------------ */
	/* Render                                                               */
	/* ------------------------------------------------------------------ */

	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'subtitle' => '',
			),
			$atts,
			self::SHORTCODE
		);

		wp_enqueue_style(
			'llm-lang-cards',
			LLM_TABELLE_URL . 'assets/llm-lang-cards.css',
			array(),
			LLM_TABELLE_VERSION
		);

		$known_lang = self::get_current_lang();
		$all_langs  = LLM_Languages::get_codes();
		$pairs      = (array) get_option( LLM_Home_Redirect::OPT_PAIRS, array() );

		$section_title = self::section_title( $known_lang );
		$subtitle      = trim( (string) $atts['subtitle'] );
		if ( '' === $subtitle ) {
			$subtitle = self::section_subtitle( $known_lang );
		}

		ob_start();
		?>
		<div class="llm-lang-cards" data-known="<?php echo esc_attr( $known_lang ); ?>">

			<?php /* ---- Selettore lingua conosciuta ---- */ ?>
			<form class="llm-lang-cards__form" method="post" action="">
				<?php wp_nonce_field( self::NONCE_ACTION_LANG, self::NONCE_FIELD_LANG ); ?>
				<input type="hidden" name="<?php echo esc_attr( self::POST_FLAG_LANG ); ?>" value="1" />
				<?php echo wp_referer_field( false ); ?>

				<div class="llm-lang-cards__form-row">
					<label class="llm-lang-cards__form-label" for="llm-known-lang-select">
						<?php echo esc_html( self::label_select( $known_lang ) ); ?>
					</label>
					<div class="llm-lang-cards__form-controls">
						<select
							id="llm-known-lang-select"
							name="llm_known_lang"
							class="llm-lang-cards__select"
						>
							<?php foreach ( $all_langs as $code => $label ) : ?>
								<option
									value="<?php echo esc_attr( $code ); ?>"
									<?php selected( $code, $known_lang ); ?>
								>
									<?php echo esc_html( self::lang_native_name( $code ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<button type="submit" class="llm-lang-cards__btn-confirm">
							<?php echo esc_html( self::label_confirm( $known_lang ) ); ?>
						</button>
					</div>
				</div>
			</form>

			<?php /* ---- Titolo sezione ---- */ ?>
			<div class="llm-lang-cards__header">
				<h2 class="llm-lang-cards__title"><?php echo esc_html( $section_title ); ?></h2>
				<?php if ( '' !== $subtitle ) : ?>
					<p class="llm-lang-cards__subtitle"><?php echo esc_html( $subtitle ); ?></p>
				<?php endif; ?>
			</div>

			<?php /* ---- Griglia card ---- */ ?>
			<div class="llm-lang-cards__grid">
				<?php
				foreach ( $all_langs as $target_code => $target_label ) {
					if ( $target_code === $known_lang ) {
						continue;
					}

					$pair_key = $known_lang . '_' . $target_code;
					$page_id  = isset( $pairs[ $pair_key ] ) ? absint( $pairs[ $pair_key ] ) : 0;

					$pair_url = '';
					if ( $page_id > 0 ) {
						$page = get_post( $page_id );
						if ( $page && 'publish' === $page->post_status ) {
							$pair_url = (string) get_permalink( $page_id );
						}
					}

					$card_title = self::card_title( $known_lang, $target_code );
					$card_desc  = self::card_desc( $known_lang, $target_code );
					$btn_label  = self::card_btn( $known_lang, $target_code );
					$available  = '' !== $pair_url;
					?>
					<div class="llm-lang-cards__card<?php echo $available ? '' : ' llm-lang-cards__card--soon'; ?>">
						<div class="llm-lang-cards__card-flag">
							<?php echo self::lang_flag( $target_code ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statico. ?>
						</div>
						<h3 class="llm-lang-cards__card-title"><?php echo esc_html( $card_title ); ?></h3>
						<p class="llm-lang-cards__card-desc"><?php echo esc_html( $card_desc ); ?></p>
						<div class="llm-lang-cards__card-footer">
							<?php if ( $available ) : ?>
								<form method="post" action="">
									<?php wp_nonce_field( self::NONCE_ACTION_CARD, self::NONCE_FIELD_CARD ); ?>
									<input type="hidden" name="<?php echo esc_attr( self::POST_FLAG_CARD ); ?>" value="1" />
									<input type="hidden" name="llm_known_lang"    value="<?php echo esc_attr( $known_lang ); ?>" />
									<input type="hidden" name="llm_learning_lang" value="<?php echo esc_attr( $target_code ); ?>" />
									<input type="hidden" name="llm_card_redirect" value="<?php echo esc_url( $pair_url ); ?>" />
									<button type="submit" class="llm-lang-cards__card-btn">
										<?php echo esc_html( $btn_label ); ?>
										<span class="llm-lang-cards__card-arrow" aria-hidden="true">→</span>
									</button>
								</form>
							<?php else : ?>
								<span class="llm-lang-cards__card-soon-badge">
									<?php echo esc_html( self::label_coming_soon( $known_lang ) ); ?>
								</span>
							<?php endif; ?>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/* ------------------------------------------------------------------ */
	/* Utility                                                              */
	/* ------------------------------------------------------------------ */

	private static function set_cookie( $name, $value ) {
		setcookie(
			$name,
			$value,
			time() + ( self::COOKIE_DAYS * DAY_IN_SECONDS ),
			COOKIEPATH ?: '/',
			COOKIE_DOMAIN ?: '',
			is_ssl(),
			true
		);
	}

	private static function get_current_lang() {
		if ( is_user_logged_in() ) {
			$lang = sanitize_key(
				(string) get_user_meta( get_current_user_id(), LLM_User_Meta::INTERFACE_LANG, true )
			);
			if ( LLM_Languages::is_valid( $lang ) ) {
				return $lang;
			}
		}

		$cookie = sanitize_key( wp_unslash( $_COOKIE[ self::COOKIE_KNOWN ] ?? '' ) );
		if ( LLM_Languages::is_valid( $cookie ) ) {
			return $cookie;
		}

		return 'it';
	}

	/* ------------------------------------------------------------------ */
	/* Nomi nativi                                                          */
	/* ------------------------------------------------------------------ */

	private static function lang_native_name( $code ) {
		$names = array(
			'it' => 'Italiano',
			'en' => 'English',
			'pl' => 'Polski',
			'es' => 'Español',
		);
		return $names[ $code ] ?? $code;
	}

	/* ------------------------------------------------------------------ */
	/* Bandierine                                                           */
	/* ------------------------------------------------------------------ */

	private static function lang_flag( $code ) {
		$flags = array(
			'it' => '🇮🇹',
			'en' => '🇬🇧',
			'pl' => '🇵🇱',
			'es' => '🇪🇸',
		);
		$emoji = $flags[ $code ] ?? '';
		return '<span class="llm-lang-cards__flag" aria-hidden="true">' . esc_html( $emoji ) . '</span>';
	}

	/* ------------------------------------------------------------------ */
	/* Traduzioni UI                                                        */
	/* ------------------------------------------------------------------ */

	private static function label_select( $lang ) {
		$t = array(
			'it' => 'Lingua che conosci:',
			'en' => 'Language you know:',
			'pl' => 'Język, który znasz:',
			'es' => 'Idioma que conoces:',
		);
		return $t[ $lang ] ?? $t['it'];
	}

	private static function label_confirm( $lang ) {
		$t = array(
			'it' => 'Conferma',
			'en' => 'Confirm',
			'pl' => 'Potwierdź',
			'es' => 'Confirmar',
		);
		return $t[ $lang ] ?? $t['it'];
	}

	private static function label_coming_soon( $lang ) {
		$t = array(
			'it' => 'Coming soon',
			'en' => 'Coming soon',
			'pl' => 'Wkrótce',
			'es' => 'Próximamente',
		);
		return $t[ $lang ] ?? $t['it'];
	}

	private static function section_title( $known ) {
		$t = array(
			'it' => 'Se conosci l\'italiano, impara:',
			'en' => 'If you know English, learn:',
			'pl' => 'Jeśli znasz polski, ucz się:',
			'es' => 'Si conoces el español, aprende:',
		);
		return $t[ $known ] ?? ( 'Se conosci ' . self::lang_native_name( $known ) . ', impara:' );
	}

	private static function section_subtitle( $known ) {
		$t = array(
			'it' => 'Scegli la lingua che vuoi imparare.',
			'en' => 'Choose the language you want to learn.',
			'pl' => 'Wybierz język, którego chcesz się uczyć.',
			'es' => 'Elige el idioma que quieres aprender.',
		);
		return $t[ $known ] ?? $t['it'];
	}

	/* ------------------------------------------------------------------ */
	/* Testi card                                                           */
	/* ------------------------------------------------------------------ */

	private static function card_title( $known, $target ) {
		$t = array(
			'it' => array(
				'en' => 'Storie per imparare l\'inglese',
				'pl' => 'Storie per imparare il polacco',
				'es' => 'Storie per imparare lo spagnolo',
			),
			'en' => array(
				'it' => 'Stories to learn Italian',
				'pl' => 'Stories to learn Polish',
				'es' => 'Stories to learn Spanish',
			),
			'pl' => array(
				'it' => 'Historie, aby nauczyć się włoskiego',
				'en' => 'Historie, aby nauczyć się angielskiego',
				'es' => 'Historie, aby nauczyć się hiszpańskiego',
			),
			'es' => array(
				'it' => 'Historias para aprender italiano',
				'en' => 'Historias para aprender inglés',
				'pl' => 'Historias para aprender polaco',
			),
		);
		return $t[ $known ][ $target ] ?? self::lang_native_name( $target );
	}

	private static function card_desc( $known, $target ) {
		$t = array(
			'it' => array(
				'en' => 'Leggi storie brevi in inglese e allena la comprensione frase per frase.',
				'pl' => 'Leggi storie brevi in polacco e allena la comprensione frase per frase.',
				'es' => 'Leggi storie brevi in spagnolo e allena la comprensione frase per frase.',
			),
			'en' => array(
				'it' => 'Read short stories in Italian and train your comprehension phrase by phrase.',
				'pl' => 'Read short stories in Polish and train your comprehension phrase by phrase.',
				'es' => 'Read short stories in Spanish and train your comprehension phrase by phrase.',
			),
			'pl' => array(
				'it' => 'Czytaj krótkie historie po włosku i ćwicz rozumienie zdanie po zdaniu.',
				'en' => 'Czytaj krótkie historie po angielsku i ćwicz rozumienie zdanie po zdaniu.',
				'es' => 'Czytaj krótkie historie po hiszpańsku i ćwicz rozumienie zdanie po zdaniu.',
			),
			'es' => array(
				'it' => 'Lee historias cortas en italiano y entrena tu comprensión frase a frase.',
				'en' => 'Lee historias cortas en inglés y entrena tu comprensión frase a frase.',
				'pl' => 'Lee historias cortas en polaco y entrena tu comprensión frase a frase.',
			),
		);
		return $t[ $known ][ $target ] ?? '';
	}

	private static function card_btn( $known, $target ) {
		$t = array(
			'it' => array(
				'en' => 'Impara l\'inglese',
				'pl' => 'Impara il polacco',
				'es' => 'Impara lo spagnolo',
			),
			'en' => array(
				'it' => 'Learn Italian',
				'pl' => 'Learn Polish',
				'es' => 'Learn Spanish',
			),
			'pl' => array(
				'it' => 'Ucz się włoskiego',
				'en' => 'Ucz się angielskiego',
				'es' => 'Ucz się hiszpańskiego',
			),
			'es' => array(
				'it' => 'Aprende italiano',
				'en' => 'Aprende inglés',
				'pl' => 'Aprende polaco',
			),
		);
		return $t[ $known ][ $target ] ?? self::lang_native_name( $target );
	}
}
