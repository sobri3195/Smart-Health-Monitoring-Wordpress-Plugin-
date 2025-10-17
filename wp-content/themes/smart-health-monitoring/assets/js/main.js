/**
 * Smart Health Monitoring Theme JavaScript
 */

(function() {
	'use strict';

	const SHMTheme = {
		init: function() {
			this.initDarkMode();
			this.initUserMenu();
			this.initMobileNav();
			this.initDateRangeSelector();
			this.initSmoothScroll();
		},

		initDarkMode: function() {
			const toggle = document.getElementById('theme-toggle');
			const html = document.documentElement;
			const lightIcon = toggle?.querySelector('.light-icon');
			const darkIcon = toggle?.querySelector('.dark-icon');

			if (!toggle) return;

			const savedTheme = localStorage.getItem('shm-theme') || 'light';
			html.setAttribute('data-theme', savedTheme);
			this.updateThemeIcon(savedTheme, lightIcon, darkIcon);

			toggle.addEventListener('click', () => {
				const currentTheme = html.getAttribute('data-theme');
				const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
				
				html.setAttribute('data-theme', newTheme);
				localStorage.setItem('shm-theme', newTheme);
				this.updateThemeIcon(newTheme, lightIcon, darkIcon);

				if (typeof shmTheme !== 'undefined' && shmTheme.userId) {
					this.saveThemePreference(newTheme);
				}
			});
		},

		updateThemeIcon: function(theme, lightIcon, darkIcon) {
			if (lightIcon && darkIcon) {
				if (theme === 'dark') {
					lightIcon.style.display = 'none';
					darkIcon.style.display = 'block';
				} else {
					lightIcon.style.display = 'block';
					darkIcon.style.display = 'none';
				}
			}
		},

		saveThemePreference: function(theme) {
			if (typeof shmTheme === 'undefined') return;

			fetch(shmTheme.ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'shm_save_theme_preference',
					theme: theme,
					nonce: shmTheme.nonce
				})
			});
		},

		initUserMenu: function() {
			const toggle = document.querySelector('.shm-user-menu-toggle');
			const dropdown = document.querySelector('.shm-user-dropdown');

			if (!toggle || !dropdown) return;

			toggle.addEventListener('click', (e) => {
				e.stopPropagation();
				const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
				toggle.setAttribute('aria-expanded', !isExpanded);
			});

			document.addEventListener('click', (e) => {
				if (!e.target.closest('.shm-user-menu')) {
					toggle.setAttribute('aria-expanded', 'false');
				}
			});
		},

		initMobileNav: function() {
			const toggle = document.querySelector('.shm-nav-toggle');
			const nav = document.getElementById('site-navigation');

			if (!toggle || !nav) return;

			toggle.addEventListener('click', () => {
				const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
				toggle.setAttribute('aria-expanded', !isExpanded);
				nav.classList.toggle('is-open');
			});
		},

		initDateRangeSelector: function() {
			const selector = document.getElementById('date-range');
			
			if (!selector) return;

			selector.addEventListener('change', (e) => {
				const range = e.target.value;
				this.loadDashboardData(range);
			});
		},

		loadDashboardData: function(range) {
			if (typeof shmTheme === 'undefined') return;

			const metrics = ['bp', 'glucose', 'activity'];
			
			metrics.forEach(metric => {
				this.fetchMetricData(metric, range);
			});
		},

		fetchMetricData: function(metric, range) {
			if (typeof shmTheme === 'undefined') return;

			fetch(`${shmTheme.restUrl}/metrics/summary?range=${range}&metrics=${metric}`, {
				headers: {
					'X-WP-Nonce': shmTheme.nonce
				}
			})
			.then(response => response.json())
			.then(data => {
				this.updateMetricCard(metric, data[metric]);
			})
			.catch(error => {
				console.error('Error fetching metric data:', error);
			});
		},

		updateMetricCard: function(metric, data) {
			console.log('Updating metric:', metric, data);
		},

		initSmoothScroll: function() {
			document.querySelectorAll('a[href^="#"]').forEach(anchor => {
				anchor.addEventListener('click', function(e) {
					const href = this.getAttribute('href');
					if (href === '#') return;

					const target = document.querySelector(href);
					if (!target) return;

					e.preventDefault();
					target.scrollIntoView({
						behavior: 'smooth',
						block: 'start'
					});
				});
			});
		}
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => SHMTheme.init());
	} else {
		SHMTheme.init();
	}

	window.SHMTheme = SHMTheme;

})();
