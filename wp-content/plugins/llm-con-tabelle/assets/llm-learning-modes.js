/* llm-learning-modes.js — barra "Modalità apprendimento" e popup di scelta. */
(function () {
	'use strict';

	var cfg = (typeof window.llmLearningModes !== 'undefined') ? window.llmLearningModes : null;
	var lastFocused = null;

	function storageKey() {
		return (cfg && cfg.storageKey) ? cfg.storageKey : 'llm_learning_mode';
	}

	function isValidMode(id) {
		if (!id || !cfg || !cfg.modes) { return false; }
		for (var i = 0; i < cfg.modes.length; i++) {
			if (cfg.modes[i].id === id) { return true; }
		}
		return false;
	}

	function readStoredMode() {
		try {
			return window.localStorage.getItem(storageKey()) || '';
		} catch (e) {
			return '';
		}
	}

	function writeStoredMode(id) {
		try {
			window.localStorage.setItem(storageKey(), id);
		} catch (e) {
			/* Storage non disponibile: la modalità resta quella di default. */
		}
	}

	/** Utenti loggati: vince il profilo. Ospiti: localStorage, poi default. */
	function resolveMode() {
		if (!cfg) { return ''; }
		if (cfg.isLoggedIn) { return cfg.current || cfg.defaultMode || ''; }
		var stored = readStoredMode();
		return isValidMode(stored) ? stored : (cfg.defaultMode || '');
	}

	function modeLabel(id) {
		if (!cfg || !cfg.modes) { return ''; }
		for (var i = 0; i < cfg.modes.length; i++) {
			if (cfg.modes[i].id === id) { return cfg.modes[i].label; }
		}
		return '';
	}

	function syncRootToMode(root, mode) {
		root.dataset.currentMode = mode;
		var valueEl = root.querySelector('.llm-learning-mode__value');
		if (valueEl) {
			var label = modeLabel(mode);
			if (label) { valueEl.textContent = label; }
		}
		root.querySelectorAll('.llm-learning-mode__radio').forEach(function (radio) {
			radio.checked = radio.value === mode;
			syncOptionActive(radio);
		});
	}

	function syncOptionActive(radio) {
		var option = radio.closest('.llm-learning-mode__option');
		if (option) {
			option.classList.toggle('llm-learning-mode__option--active', radio.checked);
		}
	}

	function overlayOf(root) {
		return root ? root.querySelector('.llm-learning-mode__overlay') : null;
	}

	function openDialog(root) {
		var overlay = overlayOf(root);
		if (!overlay) { return; }
		lastFocused = document.activeElement;
		setMessage(root, '', false);
		syncRootToMode(root, root.dataset.currentMode || resolveMode());
		overlay.hidden = false;
		var checked = overlay.querySelector('.llm-learning-mode__radio:checked');
		if (checked) { checked.focus(); }
	}

	function closeDialog(root) {
		var overlay = overlayOf(root);
		if (!overlay || overlay.hidden) { return; }
		overlay.hidden = true;
		if (lastFocused && typeof lastFocused.focus === 'function') {
			lastFocused.focus();
		}
		lastFocused = null;
	}

	function setMessage(root, text, isError) {
		var msgEl = root.querySelector('.llm-learning-mode__msg');
		if (!msgEl) { return; }
		msgEl.textContent = text || '';
		msgEl.classList.toggle('llm-learning-mode__msg--error', !!isError);
	}

	function setBusy(root, busy) {
		root.querySelectorAll('.llm-learning-mode__save, .llm-learning-mode__cancel').forEach(function (btn) {
			btn.disabled = !!busy;
		});
	}

	function selectedMode(root) {
		var checked = root.querySelector('.llm-learning-mode__radio:checked');
		return checked ? checked.value : '';
	}

	function saveMode(root) {
		var mode = selectedMode(root);
		if (!isValidMode(mode)) { return; }

		if (mode === (root.dataset.currentMode || '')) {
			closeDialog(root);
			return;
		}

		setBusy(root, true);

		if (!cfg.isLoggedIn) {
			writeStoredMode(mode);
			setMessage(root, cfg.savedMsg || '', false);
			setTimeout(function () { window.location.reload(); }, 700);
			return;
		}

		var body = new URLSearchParams();
		body.append('action', cfg.action);
		body.append('nonce', cfg.nonce);
		body.append('mode', mode);

		fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		})
			.then(function (res) { return res.json(); })
			.then(function (data) {
				if (data && data.success) {
					writeStoredMode(mode);
					setMessage(root, cfg.savedMsg || '', false);
					setTimeout(function () { window.location.reload(); }, 700);
					return;
				}
				setBusy(root, false);
				setMessage(root, cfg.errorMsg || '', true);
			})
			.catch(function () {
				setBusy(root, false);
				setMessage(root, cfg.errorMsg || '', true);
			});
	}

	function init() {
		if (!cfg) { return; }

		var roots = document.querySelectorAll('.llm-learning-mode');
		if (!roots.length) { return; }

		var mode = resolveMode();
		roots.forEach(function (root) {
			syncRootToMode(root, mode);
		});

		document.addEventListener('click', function (e) {
			var root = e.target.closest('.llm-learning-mode');
			if (!root) { return; }

			if (e.target.closest('.llm-learning-mode__change')) {
				openDialog(root);
			} else if (
				e.target.closest('.llm-learning-mode__cancel') ||
				e.target.closest('.llm-learning-mode__close')
			) {
				closeDialog(root);
			} else if (e.target.closest('.llm-learning-mode__save')) {
				saveMode(root);
			} else if (e.target.classList.contains('llm-learning-mode__overlay')) {
				closeDialog(root);
			}
		});

		document.addEventListener('change', function (e) {
			var radio = e.target.closest('.llm-learning-mode__radio');
			if (!radio) { return; }
			var root = radio.closest('.llm-learning-mode');
			if (!root) { return; }
			root.querySelectorAll('.llm-learning-mode__radio').forEach(syncOptionActive);
		});

		document.addEventListener('keydown', function (e) {
			if (e.key !== 'Escape') { return; }
			document.querySelectorAll('.llm-learning-mode').forEach(function (root) {
				closeDialog(root);
			});
		});
	}

	/** Modalità attiva, per gli altri script del gioco frasi. */
	window.llmGetLearningMode = resolveMode;

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
}());
