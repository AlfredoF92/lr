<?php
/**
 * Testi UI del gioco frasi nella “lingua che conosce” (meta utente).
 *
 * @package LLM_Tabelle
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LLM_Phrase_Game_I18n {

	/**
	 * Codice lingua UI (it|en|pl|es).
	 */
	public static function lang() {
		$code = '';
		if ( is_user_logged_in() ) {
			$code = (string) get_user_meta( get_current_user_id(), LLM_User_Meta::INTERFACE_LANG, true );
		}
		if ( ! LLM_Languages::is_valid( $code ) ) {
			$code = (string) apply_filters( 'llm_phrase_game_guest_ui_lang', 'it' );
		}
		if ( ! LLM_Languages::is_valid( $code ) ) {
			$code = 'it';
		}

		return (string) apply_filters( 'llm_phrase_game_ui_lang', $code );
	}

	/**
	 * Nome della lingua di studio (target) formulato nella lingua UI.
	 *
	 * @param string $target_code Codice lingua obiettivo (es. en).
	 */
	public static function target_lang_label_for_ui( $target_code ) {
		$target_code = sanitize_key( (string) $target_code );
		$ui          = self::lang();
		$bundles     = self::bundles();
		if ( ! isset( $bundles[ $ui ]['lang_names'][ $target_code ] ) ) {
			return isset( $bundles['it']['lang_names'][ $target_code ] )
				? $bundles['it']['lang_names'][ $target_code ]
				: $target_code;
		}
		return $bundles[ $ui ]['lang_names'][ $target_code ];
	}

	/**
	 * @param string $key Chiave stringa.
	 * @return string
	 */
	public static function get( $key ) {
		$lang = self::lang();
		$all  = self::bundles();
		if ( isset( $all[ $lang ][ $key ] ) ) {
			return $all[ $lang ][ $key ];
		}
		return isset( $all['it'][ $key ] ) ? $all['it'][ $key ] : '';
	}

	/**
	 * @param string   $key Chiave con segnaposto sprintf.
	 * @param mixed ...$args Argomenti.
	 * @return string
	 */
	public static function format( $key, ...$args ) {
		return vsprintf( self::get( $key ), $args );
	}

	/**
	 * Messaggi creativi post-sessione microfono, per tier.
	 *
	 * @return array<string, array<int, string>>
	 */
	/**
	 * @return array{phase1: array<string, array<int, string>>, phase2: array<string, array<int, string>>}
	 */
	public static function get_mic_feedback() {
		$lang = self::lang();
		$all  = self::mic_feedback_bundles();
		if ( isset( $all[ $lang ] ) ) {
			return $all[ $lang ];
		}
		return $all['it'];
	}

	/**
	 * @return array<string, array{phase1: array<string, array<int, string>>, phase2: array<string, array<int, string>>}>
	 */
	private static function mic_feedback_bundles() {
		return array(
			'it' => array(
				'phase1' => array(
					'not_started' => array(
						'Il microfono non si è attivato. Riprova.',
						'Sessione non avviata. Clicca di nuovo sul microfono.',
					),
					'silent'      => array(
						'Non ho rilevato alcun testo. Prova a scandire le parole più lentamente.',
						'Nessuna voce registrata. Articola meglio, parola per parola.',
					),
					'heard'       => array(
						'Continua pure, aggiungi altre parole se vuoi.',
						'Bene, prova ancora o vai avanti quando sei pronto.',
						'Ottimo, continua a esercitarti con il microfono.',
						'Puoi aggiungere altre parole o cliccare Continua.',
						'Prova ancora a parlare, oppure vai avanti.',
					),
				),
				'phase2' => array(
					'not_started'  => array(
						'Il microfono non si è attivato. Riprova.',
						'Sessione non avviata. Clicca di nuovo sul microfono.',
					),
					'silent'       => array(
						'Non ho rilevato alcun testo. Prova a scandire le parole più lentamente.',
						'Nessuna voce registrata. Articola meglio, parola per parola.',
					),
					'unrecognized' => array(
						'Non corrisponde alla traduzione attesa.',
						'Non contiene parole della traduzione. Riprova parola per parola.',
						'Sembra un\'altra frase. Concentrati sulla traduzione richiesta.',
					),
					'one'          => array(
						'1 parola corretta. Continua così.',
						'Buon inizio: 1 parola riconosciuta.',
					),
					'two'          => array(
						'2 parole corrette. Stai andando bene.',
						'2 parole riconosciute. Prosegui.',
					),
					'some'         => array(
						'%1$d parole corrette. Manca poco.',
						'%1$d parole riconosciute. Sei sulla strada giusta.',
					),
					'all'          => array(
						'Tutte le parole riconosciute. Ottimo lavoro.',
						'Traduzione completa al microfono. Perfetto.',
					),
				),
			),
			'en' => array(
				'phase1' => array(
					'not_started' => array(
						'The microphone did not activate. Try again.',
						'Session not started. Click the microphone again.',
					),
					'silent'      => array(
						'No text detected. Try enunciating the words more slowly.',
						'No voice recorded. Articulate each word more clearly.',
					),
					'heard'       => array(
						'Keep going, add more words if you like.',
						'Good — try again or continue when you are ready.',
						'Keep practising with the microphone.',
						'You can add more words or click Continue.',
						'Try speaking again, or move on.',
					),
				),
				'phase2' => array(
					'not_started'  => array(
						'The microphone did not activate. Try again.',
						'Session not started. Click the microphone again.',
					),
					'silent'       => array(
						'No text detected. Try enunciating the words more slowly.',
						'No voice recorded. Articulate each word more clearly.',
					),
					'unrecognized' => array(
						'It does not match the expected translation.',
						'It does not contain words from the translation. Try again word by word.',
						'It seems to be a different sentence. Focus on the required translation.',
					),
					'one'          => array(
						'1 word correct. Keep going.',
						'Good start: 1 word recognized.',
					),
					'two'          => array(
						'2 words correct. You are doing well.',
						'2 words recognized. Continue.',
					),
					'some'         => array(
						'%1$d words correct. Almost there.',
						'%1$d words recognized. You are on the right track.',
					),
					'all'          => array(
						'All words recognized. Great work.',
						'Complete translation on the microphone. Perfect.',
					),
				),
			),
			'pl' => array(
				'phase1' => array(
					'not_started' => array(
						'Mikrofon nie wystartował. Spróbuj ponownie.',
						'Sesja nie rozpoczęta. Kliknij mikrofon ponownie.',
					),
					'silent'      => array(
						'Nie wykryto tekstu. Spróbuj wymawiać słowa wolniej i wyraźniej.',
						'Nie nagrano głosu. Artykułuj lepiej, słowo po słowie.',
					),
					'heard'       => array(
						'Śmiało, dodaj więcej słów jeśli chcesz.',
						'Dobrze — spróbuj jeszcze raz lub idź dalej, gdy będziesz gotowy.',
						'Kontynuuj ćwiczenie z mikrofonem.',
						'Możesz dodać więcej słów lub kliknąć Kontynuuj.',
						'Spróbuj mówić ponownie albo przejdź dalej.',
					),
				),
				'phase2' => array(
					'not_started'  => array(
						'Mikrofon nie wystartował. Spróbuj ponownie.',
						'Sesja nie rozpoczęta. Kliknij mikrofon ponownie.',
					),
					'silent'       => array(
						'Nie wykryto tekstu. Spróbuj wymawiać słowa wolniej i wyraźniej.',
						'Nie nagrano głosu. Artykułuj lepiej, słowo po słowie.',
					),
					'unrecognized' => array(
						'Nie pasuje do oczekiwanego tłumaczenia.',
						'Nie zawiera słów z tłumaczenia. Spróbuj słowo po słowie.',
						'To wygląda na inne zdanie. Skup się na wymaganym tłumaczeniu.',
					),
					'one'          => array(
						'1 słowo poprawne. Tak trzymaj.',
						'Dobry początek: 1 słowo rozpoznane.',
					),
					'two'          => array(
						'2 słowa poprawne. Idzie ci dobrze.',
						'2 słowa rozpoznane. Kontynuuj.',
					),
					'some'         => array(
						'%1$d słów poprawnych. Prawie gotowe.',
						'%1$d słów rozpoznanych. Jesteś na dobrej drodze.',
					),
					'all'          => array(
						'Wszystkie słowa rozpoznane. Świetna robota.',
						'Pełne tłumaczenie przy mikrofonie. Idealnie.',
					),
				),
			),
			'es' => array(
				'phase1' => array(
					'not_started' => array(
						'El micrófono no se activó. Inténtalo de nuevo.',
						'Sesión no iniciada. Haz clic de nuevo en el micrófono.',
					),
					'silent'      => array(
						'No se detectó texto. Intenta articular las palabras más despacio.',
						'No se registró voz. Pronuncia cada palabra con más claridad.',
					),
					'heard'       => array(
						'Sigue, añade más palabras si quieres.',
						'Bien — prueba otra vez o continúa cuando estés listo.',
						'Sigue practicando con el micrófono.',
						'Puedes añadir más palabras o pulsar Continuar.',
						'Prueba a hablar de nuevo, o sigue adelante.',
					),
				),
				'phase2' => array(
					'not_started'  => array(
						'El micrófono no se activó. Inténtalo de nuevo.',
						'Sesión no iniciada. Haz clic de nuevo en el micrófono.',
					),
					'silent'       => array(
						'No se detectó texto. Intenta articular las palabras más despacio.',
						'No se registró voz. Pronuncia cada palabra con más claridad.',
					),
					'unrecognized' => array(
						'No coincide con la traducción esperada.',
						'No contiene palabras de la traducción. Inténtalo palabra por palabra.',
						'Parece otra frase. Concéntrate en la traducción requerida.',
					),
					'one'          => array(
						'1 palabra correcta. Sigue así.',
						'Buen inicio: 1 palabra reconocida.',
					),
					'two'          => array(
						'2 palabras correctas. Lo estás haciendo bien.',
						'2 palabras reconocidas. Continúa.',
					),
					'some'         => array(
						'%1$d palabras correctas. Casi lo tienes.',
						'%1$d palabras reconocidas. Vas por buen camino.',
					),
					'all'          => array(
						'Todas las palabras reconocidas. Excelente trabajo.',
						'Traducción completa al micrófono. Perfecto.',
					),
				),
			),
		);
	}

	/**
	 * @return array<string, array<string, string>>
	 */
	private static function bundles() {
		static $cache = null;
		if ( null !== $cache ) {
			return $cache;
		}

		$names_it = array(
			'en' => 'inglese',
			'it' => 'italiano',
			'pl' => 'polacco',
			'es' => 'spagnolo',
		);
		$names_en = array(
			'en' => 'English',
			'it' => 'Italian',
			'pl' => 'Polish',
			'es' => 'Spanish',
		);
		$names_pl = array(
			'en' => 'angielski',
			'it' => 'włoski',
			'pl' => 'polski',
			'es' => 'hiszpański',
		);
		$names_es = array(
			'en' => 'inglés',
			'it' => 'italiano',
			'pl' => 'polaco',
			'es' => 'español',
		);

		$cache = array(
			'it' => array(
				'lang_names'            => $names_it,
				'story_unavailable'     => 'Storia non disponibile.',
				'no_phrases'            => 'Nessuna frase impostata per questa storia.',
				'story_section_title'   => 'La tua storia (traduzioni completate)',
				'sr_your_translation'   => 'La tua traduzione',
				'continue'              => 'Continua',
				'bravo_intro'           => 'Bravo! Per questa frase ti consiglio:',
				'label_main'            => 'La traduzione principale consigliata corretta:',
				'peek_target_label'     => 'Dai uno sguardo alla traduzione',
				'peek_target_aria'      => 'Mostra la traduzione principale per 10 secondi',
				'label_alt'             => 'Note di approfondimento: ',
				'alt_toggle_show'       => 'Mostra note di approfondimento',
				'alt_toggle_hide'       => 'Nascondi note di approfondimento',
				'sr_rewrite'            => 'Riscrivi la frase',
				'done_all'              => 'Hai completato tutte le frasi di questa storia.',
				'translate_prompt'      => 'FASE 1 - Traduci la frase in %s. Gli errori sono benvenuti! Se non conosci le parole, aiutati con l\'ascolto della traduzione e poi prova a ripeterla.',
				'rewrite_prompt'        => 'Step 2 - Ora traduci la frase nel modo corretto! (Usa la traduzione principale consigliata)',
				'input_placeholder_phase1' => 'Pronuncia o scrivi la traduzione in %s della frase',
				'input_placeholder_phase2' => 'Pronuncia o scrivi la traduzione in %s della frase',
				'phase1_fail'           => 'Per favore prova a scrivere qualche parola corretta per andare avanti... prova ad aiutarti con l\'ascolto',
				'phase2_fail'           => 'La frase deve coincidere con la traduzione principale (ignorando punteggiatura e simboli). Riprova.',
			'phase2_complete'       => 'Frase completata.',
			'phase2_story_continue' => 'Bravo! Traduzione corretta. Ottimo lavoro. 1 punto per te! Andiamo avanti con la storia...',
			'phase2_checking'       => 'Verifica in corso…',
			'bravo_correct'         => 'Bravo! Ottimo lavoro! La traduzione è corretta.',
			'phrase_complete_points' => 'Frase completata: +1 punto',
			'mic_used_point'        => 'Microfono utilizzato: +1 punto',
			'mic_used_no_point'     => 'Microfono utilizzato: +0 punti',
			'story_continue'        => 'Andiamo avanti con la storia…',
				'empty_input'           => 'Scrivi qualcosa nell’area di testo.',
				'progress'              => 'Frase %1$d di %2$d',
				'ajax_error'            => 'Errore di rete. Riprova.',
				'invalid_story'         => 'Storia non valida.',
				'phrase_not_found'      => 'Frase non trovata.',
				'bad_request'           => 'Richiesta non valida.',
				'your_phrase_label'     => 'La tua frase:',
				'mic_button'            => 'Attiva microfono',
				'sr_mic'                => 'Attiva il microfono per dettare nel campo di testo.',
				'listen_target_aria'    => 'Ascolta la traduzione in %s (lettura lenta)',
				'listen_target_label'   => 'Ascolta la traduzione',
				'listen_replay_after_mic' => 'Vuoi riascoltare la traduzione dopo ogni sessione microfono?',
				'listen_replay_yes'     => 'Sì',
				'listen_replay_no'      => 'No',
				'story_progress_restart' => 'Ricomincia storia',
				'story_progress_guest'  => 'Accedi per vedere i progressi e ricominciare la storia.',
				'story_progress_confirm' => 'Ricominciare dalla prima frase? Il gioco riparte da capo; le frasi già completate restano salvate (e i coin non cambiano).',
			'story_progress_sr'     => 'Progresso storia: %1$d frasi su %2$d completate',
			'intro_label'           => 'Introduzione:',
			'mic_hint'              => 'Clicca per parlare',
			'mic_pending'           => 'Avvio microfono... Fai un bel respiro :)',
			'mic_listening'         => 'Parla ora, ti ascolto…',
			'mic_grace'             => 'Finisco di ascoltare…',
			'mic_denied'            => 'Microfono non autorizzato. Abilita il microfono nelle impostazioni del browser.',
			'mic_unavailable'       => 'Microfono non disponibile sul dispositivo.',
			'mic_no_audio'          => 'Nessun audio rilevato. Riprova.',
			'clear_input'           => 'Ricomincia da capo',
			'loading_notes'         => 'Carico gli appunti per questa frase',
			'resolve_go_prompt'     => 'Traduci la frase in %s. Per andare avanti la traduzione deve essere corretta: aiutati con l\'ascolto e con gli appunti.',
			'notes_toggle_show'     => 'Carica gli appunti per questa frase',
			'notes_toggle_hide'     => 'Nascondi gli appunti',
			'resolve_go_fail'       => 'Non è ancora la traduzione corretta. Apri gli appunti qui sotto per rivedere la frase e riprova.',
			'learning_mode_label'   => 'Modalità apprendimento:',
			'learning_mode_change'  => 'Cambia',
			'learning_mode_title'   => 'Scegli la modalità di apprendimento',
			'learning_mode_intro'   => 'Scegli come vuoi allenarti. Puoi cambiare quando vuoi: riprenderai dalla frase in corso.',
			'learning_mode_save'    => 'Conferma e Salva',
			'learning_mode_cancel'  => 'Annulla',
			'learning_mode_close'   => 'Chiudi',
			'learning_mode_saved'   => 'Modalità salvata. Aggiorno la pagina…',
			'learning_mode_error'   => 'Non è stato possibile salvare la modalità. Riprova.',
			'mode_loverewrite_label' => 'LoveRewrite',
			'mode_loverewrite_desc' => 'Due fasi per ogni frase: prima provi la tua traduzione, poi leggi gli appunti e riscrivi la frase corretta.',
			'mode_resolve_go_label' => 'Risolvi e vai',
			'mode_resolve_go_desc'  => 'Una sola fase: scrivi la traduzione corretta e passi subito alla frase successiva. Gli appunti li apri tu quando vuoi.',
			'mode_read_go_fast_label' => 'Read and go fast',
			'mode_read_go_fast_desc'  => 'Nessun controllo: leggi la frase, provi la traduzione se vuoi e vai avanti. Ogni frase vale sempre 1 punto.',
			'read_go_fast_prompt'   => 'Traduci la frase in %s, oppure leggila e basta: quando vuoi vai avanti. Aiutati con l\'ascolto e con gli appunti.',
			'read_go_fast_next'     => 'Vai alla prossima frase',
			'read_go_fast_target'   => 'La traduzione principale consigliata per questa frase è:',
			'read_go_fast_complete' => '+1 punto, passiamo alla frase successiva…',
		),
		'en' => array(
				'lang_names'            => $names_en,
				'story_unavailable'     => 'Story unavailable.',
				'no_phrases'            => 'No phrases configured for this story.',
				'story_section_title'   => 'Your story (completed translations)',
				'sr_your_translation'   => 'Your translation',
				'continue'              => 'Continue',
				'bravo_intro'           => 'Well done! For this phrase we suggest:',
				'label_main'            => 'Recommended correct translation:',
				'peek_target_label'     => 'Take a look at the translation',
				'peek_target_aria'      => 'Show the main translation for 10 seconds',
				'label_alt'             => 'Further notes: ',
				'alt_toggle_show'       => 'Show further notes',
				'alt_toggle_hide'       => 'Hide further notes',
				'sr_rewrite'            => 'Rewrite the sentence',
				'done_all'              => 'You have completed all phrases in this story.',
				'translate_prompt'      => 'PHASE 1 - Translate the sentence into %s. Mistakes are welcome! If you do not know the words, use the audio translation and then try to repeat it.',
				'rewrite_prompt'        => 'PHASE 2 - Now translate the sentence correctly! Compare your answer with the suggested translation in green: it must match word for word. Accents and punctuation are not required. Keep practising until you get it!',
				'input_placeholder_phase1' => 'Say or type the translation in %s of the sentence',
				'input_placeholder_phase2' => 'Say or type the translation in %s of the sentence',
				'phase1_fail'           => 'Please try to write at least a few correct words to continue... try listening to the audio for help',
				'phase2_fail'           => 'The sentence must match the main translation (ignoring punctuation and symbols). Try again.',
			'phase2_complete'       => 'Sentence completed.',
			'phase2_story_continue' => 'Great! Correct translation. Excellent work. 1 point for you! Let us continue the story...',
			'phase2_checking'       => 'Checking…',
			'bravo_correct'         => 'Well done! Great job! The translation is correct.',
			'phrase_complete_points' => 'Phrase completed: +1 point',
			'mic_used_point'        => 'Microphone used: +1 point',
			'mic_used_no_point'     => 'Microphone used: +0 points',
			'story_continue'        => 'Let\'s continue the story…',
				'empty_input'           => 'Type something in the text area.',
				'progress'              => 'Phrase %1$d of %2$d',
				'ajax_error'            => 'Network error. Please try again.',
				'invalid_story'         => 'Invalid story.',
				'phrase_not_found'      => 'Phrase not found.',
				'bad_request'           => 'Invalid request.',
				'your_phrase_label'     => 'Your sentence:',
				'mic_button'            => 'Activate microphone',
				'sr_mic'                => 'Activate the microphone to dictate into the text field.',
				'listen_target_aria'    => 'Listen to the translation in %s (slow)',
				'listen_target_label'   => 'Listen to the translation',
				'listen_replay_after_mic' => 'Replay the translation after each microphone session?',
				'listen_replay_yes'     => 'Yes',
				'listen_replay_no'      => 'No',
				'story_progress_restart' => 'Restart story',
				'story_progress_guest'  => 'Log in to see progress and restart the story.',
				'story_progress_confirm' => 'Start again from the first phrase? The game restarts from the beginning; completed phrases stay saved. Your coins will not change.',
			'story_progress_sr'     => 'Story progress: %1$d of %2$d phrases completed',
			'intro_label'           => 'Introduction:',
			'mic_hint'              => 'Click to speak',
			'mic_pending'           => 'Starting microphone…',
			'mic_listening'         => 'Listening…',
			'mic_grace'             => 'Finishing…',
			'mic_denied'            => 'Microphone not allowed. Enable it in your browser settings.',
			'mic_unavailable'       => 'Microphone not available on this device.',
			'mic_no_audio'          => 'No audio detected. Try again.',
			'clear_input'           => 'Start over',
			'loading_notes'         => 'Loading the notes for this phrase',
			'resolve_go_prompt'     => 'Translate the sentence into %s. To move on, the translation must be correct: use the audio and the notes to help you.',
			'notes_toggle_show'     => 'Load the notes for this sentence',
			'notes_toggle_hide'     => 'Hide the notes',
			'resolve_go_fail'       => 'Not the correct translation yet. Open the notes below to review the sentence and try again.',
			'learning_mode_label'   => 'Learning mode:',
			'learning_mode_change'  => 'Change',
			'learning_mode_title'   => 'Choose your learning mode',
			'learning_mode_intro'   => 'Choose how you want to practise. You can change it anytime: you will resume from the current sentence.',
			'learning_mode_save'    => 'Confirm and save',
			'learning_mode_cancel'  => 'Cancel',
			'learning_mode_close'   => 'Close',
			'learning_mode_saved'   => 'Mode saved. Reloading the page…',
			'learning_mode_error'   => 'The mode could not be saved. Please try again.',
			'mode_loverewrite_label' => 'LoveRewrite',
			'mode_loverewrite_desc' => 'Two steps per sentence: first try your own translation, then read the notes and rewrite the correct sentence.',
			'mode_resolve_go_label' => 'Solve and go',
			'mode_resolve_go_desc'  => 'A single step: write the correct translation and move straight to the next sentence. You open the notes whenever you want.',
			'mode_read_go_fast_label' => 'Read and go fast',
			'mode_read_go_fast_desc'  => 'No checks: read the sentence, try the translation if you feel like it, and move on. Every sentence is always worth 1 point.',
			'read_go_fast_prompt'   => 'Translate the sentence into %s, or just read it: move on whenever you want. Use the audio and the notes to help you.',
			'read_go_fast_next'     => 'Go to the next sentence',
			'read_go_fast_target'   => 'The recommended main translation for this sentence is:',
			'read_go_fast_complete' => '+1 point, on to the next sentence…',
		),
		'pl' => array(
				'lang_names'            => $names_pl,
				'story_unavailable'     => 'Opowieść jest niedostępna.',
				'no_phrases'            => 'Brak zdań skonfigurowanych dla tej opowieści.',
				'story_section_title'   => 'Twoja historia (ukończone tłumaczenia)',
				'sr_your_translation'   => 'Twoje tłumaczenie',
				'continue'              => 'Dalej',
				'bravo_intro'           => 'Brawo! Dla tej frazy polecamy:',
				'label_main'            => 'Zalecane poprawne tłumaczenie:',
				'peek_target_label'     => 'Spójrz na tłumaczenie',
				'peek_target_aria'      => 'Pokaż główne tłumaczenie na 10 sekund',
				'label_alt'             => 'Notatki uzupełniające: ',
				'alt_toggle_show'       => 'Pokaż notatki uzupełniające',
				'alt_toggle_hide'       => 'Ukryj notatki uzupełniające',
				'sr_rewrite'            => 'Przepisz zdanie',
				'done_all'              => 'Ukończyłeś wszystkie zdania tej opowieści.',
				'translate_prompt'      => 'FAZA 1 - Przetłumacz zdanie na %s. Błędy są mile widziane! Jeśli nie znasz słów, posłuchaj tłumaczenia i spróbuj je powtórzyć.',
				'rewrite_prompt'        => 'FAZA 2 - Teraz przetłumacz zdanie poprawnie! Porównaj swoją odpowiedź z sugerowanym tłumaczeniem na zielono: musi się zgadzać słowo po słowie. Akcenty i interpunkcja nie są wymagane. Ćwicz, aż Ci się uda!',
				'input_placeholder_phase1' => 'Wypowiedz lub napisz tłumaczenie na %s zdania',
				'input_placeholder_phase2' => 'Wypowiedz lub napisz tłumaczenie na %s zdania',
				'phase1_fail'           => 'Spróbuj napisać kilka poprawnych słów, żeby przejść dalej... posłuchaj nagrania, żeby się podpowiedzieć',
				'phase2_fail'           => 'Zdanie musi być zgodne z głównym tłumaczeniem (ignorując interpunkcję i symbole). Spróbuj ponownie.',
			'phase2_complete'       => 'Zdanie ukończone.',
			'phase2_story_continue' => 'Brawo! Poprawne tlumaczenie. Swietna robota. 1 punkt dla Ciebie! Kontynuujmy historie...',
			'phase2_checking'       => 'Sprawdzanie…',
			'bravo_correct'         => 'Brawo! Swietna robota! Tlumaczenie jest poprawne.',
			'phrase_complete_points' => 'Zdanie ukonczone: +1 punkt',
			'mic_used_point'        => 'Mikrofon uzyty: +1 punkt',
			'mic_used_no_point'     => 'Mikrofon uzyty: +0 punktow',
			'story_continue'        => 'Kontynuujmy historie…',
				'empty_input'           => 'Wpisz coś w polu tekstowym.',
				'progress'              => 'Zdanie %1$d z %2$d',
				'ajax_error'            => 'Błąd sieci. Spróbuj ponownie.',
				'invalid_story'         => 'Nieprawidłowa opowieść.',
				'phrase_not_found'      => 'Nie znaleziono zdania.',
				'bad_request'           => 'Nieprawidłowe żądanie.',
				'your_phrase_label'     => 'Twoje zdanie:',
				'mic_button'            => 'Aktywuj mikrofon',
				'sr_mic'                => 'Aktywuj mikrofon, aby dyktować w polu tekstowym.',
				'listen_target_aria'    => 'Posłuchaj tłumaczenia po %s (wolno)',
				'listen_target_label'   => 'Posłuchaj tłumaczenia',
				'listen_replay_after_mic' => 'Odtworzyć tłumaczenie po każdej sesji mikrofonu?',
				'listen_replay_yes'     => 'Tak',
				'listen_replay_no'      => 'Nie',
				'story_progress_restart' => 'Zacznij od nowa',
				'story_progress_guest'  => 'Zaloguj się, aby zobaczyć postęp i zacząć opowieść od nowa.',
				'story_progress_confirm' => 'Zacząć od pierwszego zdania? Gra wraca na początek; ukończone zdania pozostają zapisane. Monety się nie zmienią.',
			'story_progress_sr'     => 'Postęp: ukończono %1$d z %2$d zdań',
			'intro_label'           => 'Wstęp:',
			'mic_hint'              => 'Kliknij, aby mówić',
			'mic_pending'           => 'Uruchamiam mikrofon…',
			'mic_listening'         => 'Słucham…',
			'mic_grace'             => 'Kończę nagrywanie…',
			'mic_denied'            => 'Brak dostępu do mikrofonu. Włącz go w ustawieniach przeglądarki.',
			'mic_unavailable'       => 'Mikrofon niedostępny na tym urządzeniu.',
			'mic_no_audio'          => 'Nie wykryto dźwięku. Spróbuj ponownie.',
			'clear_input'           => 'Zacznij od nowa',
			'loading_notes'         => 'Ładuję notatki do tego zdania',
			'resolve_go_prompt'     => 'Przetłumacz zdanie na %s. Aby przejść dalej, tłumaczenie musi być poprawne: pomóż sobie nagraniem i notatkami.',
			'notes_toggle_show'     => 'Wczytaj notatki do tego zdania',
			'notes_toggle_hide'     => 'Ukryj notatki',
			'resolve_go_fail'       => 'To jeszcze nie jest poprawne tłumaczenie. Otwórz notatki poniżej, przejrzyj zdanie i spróbuj ponownie.',
			'learning_mode_label'   => 'Tryb nauki:',
			'learning_mode_change'  => 'Zmień',
			'learning_mode_title'   => 'Wybierz tryb nauki',
			'learning_mode_intro'   => 'Wybierz sposób ćwiczenia. Możesz to zmienić w każdej chwili: wrócisz do bieżącego zdania.',
			'learning_mode_save'    => 'Potwierdź i zapisz',
			'learning_mode_cancel'  => 'Anuluj',
			'learning_mode_close'   => 'Zamknij',
			'learning_mode_saved'   => 'Tryb zapisany. Odświeżam stronę…',
			'learning_mode_error'   => 'Nie udało się zapisać trybu. Spróbuj ponownie.',
			'mode_loverewrite_label' => 'LoveRewrite',
			'mode_loverewrite_desc' => 'Dwa etapy na zdanie: najpierw próbujesz własnego tłumaczenia, potem czytasz notatki i zapisujesz poprawne zdanie.',
			'mode_resolve_go_label' => 'Rozwiąż i dalej',
			'mode_resolve_go_desc'  => 'Jeden etap: wpisujesz poprawne tłumaczenie i od razu przechodzisz do następnego zdania. Notatki otwierasz, kiedy chcesz.',
			'mode_read_go_fast_label' => 'Read and go fast',
			'mode_read_go_fast_desc'  => 'Bez sprawdzania: czytasz zdanie, próbujesz tłumaczenia, jeśli chcesz, i idziesz dalej. Każde zdanie zawsze daje 1 punkt.',
			'read_go_fast_prompt'   => 'Przetłumacz zdanie na %s albo po prostu je przeczytaj: dalej przechodzisz, kiedy chcesz. Pomóż sobie nagraniem i notatkami.',
			'read_go_fast_next'     => 'Przejdź do następnego zdania',
			'read_go_fast_target'   => 'Zalecane główne tłumaczenie tego zdania to:',
			'read_go_fast_complete' => '+1 punkt, przechodzimy do następnego zdania…',
		),
		'es' => array(
				'lang_names'            => $names_es,
				'story_unavailable'     => 'Historia no disponible.',
				'no_phrases'            => 'No hay frases configuradas para esta historia.',
				'story_section_title'   => 'Tu historia (traducciones completadas)',
				'sr_your_translation'   => 'Tu traducción',
				'continue'              => 'Continuar',
				'bravo_intro'           => '¡Bien hecho! Para esta frase te recomendamos:',
				'label_main'            => 'Traducción correcta recomendada:',
				'peek_target_label'     => 'Echa un vistazo a la traducción',
				'peek_target_aria'      => 'Mostrar la traducción principal durante 10 segundos',
				'label_alt'             => 'Notas de profundización: ',
				'alt_toggle_show'       => 'Mostrar notas de profundización',
				'alt_toggle_hide'       => 'Ocultar notas de profundización',
				'sr_rewrite'            => 'Reescribe la frase',
				'done_all'              => 'Has completado todas las frases de esta historia.',
				'translate_prompt'      => 'FASE 1 - Traduce la frase al %s. ¡Los errores son bienvenidos! Si no conoces las palabras, escucha la traducción y luego intenta repetirla.',
				'rewrite_prompt'        => 'FASE 2 - ¡Ahora traduce la frase correctamente! Compara tu respuesta con la traducción sugerida en verde: debe coincidir palabra por palabra. Los acentos y la puntuación no son obligatorios. ¡Sigue practicando hasta conseguirlo!',
				'input_placeholder_phase1' => 'Di o escribe la traducción al %s de la frase',
				'input_placeholder_phase2' => 'Di o escribe la traducción al %s de la frase',
				'phase1_fail'           => 'Por favor intenta escribir algunas palabras correctas para continuar... escucha el audio para ayudarte',
				'phase2_fail'           => 'La frase debe coincidir con la traducción principal (ignorando puntuación y símbolos). Inténtalo de nuevo.',
			'phase2_complete'       => 'Frase completada.',
			'phase2_story_continue' => '¡Bien hecho! Traduccion correcta. Excelente trabajo. ¡1 punto para ti! Continuemos la historia...',
			'phase2_checking'       => 'Verificando…',
			'bravo_correct'         => '¡Bien hecho! ¡Excelente trabajo! La traducción es correcta.',
			'phrase_complete_points' => 'Frase completada: +1 punto',
			'mic_used_point'        => 'Micrófono utilizado: +1 punto',
			'mic_used_no_point'     => 'Micrófono utilizado: +0 puntos',
			'story_continue'        => 'Continuemos la historia…',
				'empty_input'           => 'Escribe algo en el cuadro de texto.',
				'progress'              => 'Frase %1$d de %2$d',
				'ajax_error'            => 'Error de red. Vuelve a intentarlo.',
				'invalid_story'         => 'Historia no válida.',
				'phrase_not_found'      => 'Frase no encontrada.',
				'bad_request'           => 'Solicitud no válida.',
				'your_phrase_label'     => 'Tu frase:',
				'mic_button'            => 'Activar micrófono',
				'sr_mic'                => 'Activa el micrófono para dictar en el cuadro de texto.',
				'listen_target_aria'    => 'Escucha la traducción en %s (lento)',
				'listen_target_label'   => 'Escucha la traducción',
				'listen_replay_after_mic' => '¿Reescuchar la traducción tras cada sesión de micrófono?',
				'listen_replay_yes'     => 'Sí',
				'listen_replay_no'      => 'No',
				'story_progress_restart' => 'Reiniciar historia',
				'story_progress_guest'  => 'Inicia sesión para ver el progreso y reiniciar la historia.',
				'story_progress_confirm' => '¿Volver a la primera frase? El juego empieza de nuevo; las frases completadas siguen guardadas. Las monedas no cambian.',
			'story_progress_sr'     => 'Progreso: %1$d de %2$d frases completadas',
			'intro_label'           => 'Introducción:',
			'mic_hint'              => 'Haz clic para hablar',
			'mic_pending'           => 'Iniciando micrófono…',
			'mic_listening'         => 'Escuchando…',
			'mic_grace'             => 'Terminando…',
			'mic_denied'            => 'Micrófono no autorizado. Actívalo en la configuración del navegador.',
			'mic_unavailable'       => 'Micrófono no disponible en este dispositivo.',
			'mic_no_audio'          => 'No se detectó audio. Inténtalo de nuevo.',
			'clear_input'           => 'Empezar de nuevo',
			'loading_notes'         => 'Cargando las notas de esta frase',
			'resolve_go_prompt'     => 'Traduce la frase al %s. Para avanzar la traducción debe ser correcta: apóyate en el audio y en las notas.',
			'notes_toggle_show'     => 'Carga las notas de esta frase',
			'notes_toggle_hide'     => 'Ocultar las notas',
			'resolve_go_fail'       => 'Todavía no es la traducción correcta. Abre las notas de abajo para repasar la frase e inténtalo de nuevo.',
			'learning_mode_label'   => 'Modo de aprendizaje:',
			'learning_mode_change'  => 'Cambiar',
			'learning_mode_title'   => 'Elige tu modo de aprendizaje',
			'learning_mode_intro'   => 'Elige cómo quieres practicar. Puedes cambiarlo cuando quieras: retomarás desde la frase actual.',
			'learning_mode_save'    => 'Confirmar y guardar',
			'learning_mode_cancel'  => 'Cancelar',
			'learning_mode_close'   => 'Cerrar',
			'learning_mode_saved'   => 'Modo guardado. Actualizando la página…',
			'learning_mode_error'   => 'No se ha podido guardar el modo. Inténtalo de nuevo.',
			'mode_loverewrite_label' => 'LoveRewrite',
			'mode_loverewrite_desc' => 'Dos fases por frase: primero pruebas tu traducción y luego lees las notas y reescribes la frase correcta.',
			'mode_resolve_go_label' => 'Resuelve y sigue',
			'mode_resolve_go_desc'  => 'Una sola fase: escribes la traducción correcta y pasas directamente a la siguiente frase. Las notas las abres cuando quieras.',
			'mode_read_go_fast_label' => 'Read and go fast',
			'mode_read_go_fast_desc'  => 'Sin comprobaciones: lees la frase, pruebas la traducción si quieres y sigues adelante. Cada frase vale siempre 1 punto.',
			'read_go_fast_prompt'   => 'Traduce la frase al %s, o simplemente léela: avanzas cuando quieras. Apóyate en el audio y en las notas.',
			'read_go_fast_next'     => 'Ir a la siguiente frase',
			'read_go_fast_target'   => 'La traducción principal recomendada para esta frase es:',
			'read_go_fast_complete' => '+1 punto, pasamos a la siguiente frase…',
		),
	);

		return $cache;
	}
}
