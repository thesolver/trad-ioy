(function () {
	'use strict';

	function padNumber(n, len) {
		var s = n.toString();
		if (s.length < len) {
			s = ('0000000000' + s).slice(-len);
		}
		return s;
	}

	function getCookie(name) {
		var match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
		return match ? decodeURIComponent(match[1]) : null;
	}

	function setCookie(name, value, days) {
		var expires = '';
		if (days) {
			var date = new Date();
			date.setTime(date.getTime() + days * 86400000);
			expires = '; expires=' + date.toUTCString();
		}
		var secure = window.location.protocol === 'https:' ? '; Secure' : '';
		document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/; SameSite=Lax' + secure;
	}

	function isAllowedVersion(code, allowed) {
		return allowed.indexOf(code) !== -1;
	}

	function todayClass() {
		var now = new Date();
		var month = now.getMonth() + 1;
		var day = now.getDate();
		if (month === 2 && day === 29) {
			day = 28;
		}
		return 'trad-ioy-day-' + padNumber(month, 2) + padNumber(day, 2);
	}

	function scrollToToday(selector) {
		var el = document.querySelector(selector);
		if (!el) {
			return;
		}
		var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		el.scrollIntoView({ block: 'start', behavior: reduceMotion ? 'auto' : 'smooth' });
	}

	function replaceVersionInLinks(fromVersion, toVersion) {
		var pattern = new RegExp('/books/' + fromVersion.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '/', 'g');
		document.querySelectorAll('a.trad-ioy-reflink').forEach(function (link) {
			if (link.href) {
				link.href = link.href.replace(pattern, '/books/' + toVersion + '/');
			}
		});
	}

	function initTable(table, config) {
		var defaultVersion = config.bibleVersion;
		var activeVersion = defaultVersion;
		var templateVersion = 'esv';

		if (config.cookie) {
			var stored = getCookie('trad_ioy_bible_version');
			if (stored && isAllowedVersion(stored, config.allowedVersions)) {
				activeVersion = stored;
			} else {
				setCookie('trad_ioy_bible_version', defaultVersion, 365);
			}
		}

		if (activeVersion !== templateVersion) {
			replaceVersionInLinks(templateVersion, activeVersion);
		}

		var select = table.closest('.trad-ioy-content').querySelector('select.trad-ioy-bible-version');
		if (select) {
			select.value = activeVersion;
			select.addEventListener('change', function () {
				var next = select.value;
				if (!isAllowedVersion(next, config.allowedVersions)) {
					return;
				}
				var prev = activeVersion;
				activeVersion = next;
				if (config.cookie) {
					setCookie('trad_ioy_bible_version', next, 365);
				}
				replaceVersionInLinks(prev, next);
			});
		}

		var dayClass = todayClass();
		var todayRows = table.querySelectorAll('tr.' + dayClass);
		todayRows.forEach(function (row) {
			row.classList.add('trad-ioy-is-today');
			row.setAttribute('aria-current', 'date');
		});

		if (config.scroll) {
			scrollToToday('tr.' + dayClass);
		}

		if (config.copyTodayRow) {
			todayRows.forEach(function (row) {
				var clone = row.cloneNode(true);
				row.parentNode.insertBefore(clone, row.parentNode.firstChild);
			});
		}
	}

	function onReady() {
		if (typeof tradIoyConfig === 'undefined') {
			return;
		}
		document.querySelectorAll('table.trad-ioy-reading-plan').forEach(function (table) {
			initTable(table, tradIoyConfig);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', onReady);
	} else {
		onReady();
	}
})();
