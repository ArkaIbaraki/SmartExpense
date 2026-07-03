</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
	const body = document.body;
	const toggleButton = document.querySelector('[data-sidebar-toggle]');
	const storageKey = 'sd-sidebar-state';

	const isMobile = () => window.matchMedia('(max-width: 991.98px)').matches;

	const applyState = (collapsed) => {
		body.classList.toggle('sd-sidebar-collapsed', collapsed);
		body.classList.toggle('sd-sidebar-expanded', !collapsed && isMobile());

		if (toggleButton) {
			toggleButton.setAttribute('aria-expanded', String(!collapsed));
		}
	};

	const storedState = localStorage.getItem(storageKey);
	if (storedState === 'collapsed') {
		applyState(true);
	} else if (storedState === 'expanded') {
		applyState(false);
	} else if (isMobile()) {
		applyState(true);
	}

	if (toggleButton) {
		toggleButton.addEventListener('click', () => {
			const nextCollapsed = !body.classList.contains('sd-sidebar-collapsed');
			body.classList.toggle('sd-sidebar-collapsed', nextCollapsed);
			body.classList.toggle('sd-sidebar-expanded', !nextCollapsed && isMobile());
			toggleButton.setAttribute('aria-expanded', String(!nextCollapsed));
			localStorage.setItem(storageKey, nextCollapsed ? 'collapsed' : 'expanded');
		});
	}

	window.addEventListener('resize', () => {
		if (localStorage.getItem(storageKey) !== null) {
			return;
		}

		if (isMobile()) {
			applyState(true);
		} else {
			applyState(false);
		}
	});
})();
</script>
</body>
</html>
