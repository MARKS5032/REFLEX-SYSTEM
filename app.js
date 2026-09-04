/**
 * REFLEX - small progressive-enhancement helpers.
 * The app works fully without JS (all forms are real <form> POSTs);
 * this file only smooths out the experience.
 */

document.addEventListener('DOMContentLoaded', function () {
  // Auto-dismiss success alerts after a few seconds.
  document.querySelectorAll('.alert-success').forEach(function (alertEl) {
    setTimeout(function () {
      var alert = bootstrap.Alert.getOrCreateInstance(alertEl);
      alert.close();
    }, 5000);
  });

  // Confirm before destructive/important actions (e.g. assigning a rider,
  // marking delivered) when an element has [data-confirm="message"].
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('submit', function (e) {
      if (!window.confirm(el.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
    el.addEventListener('click', function (e) {
      if (el.tagName === 'A' || el.tagName === 'BUTTON') {
        if (!window.confirm(el.getAttribute('data-confirm'))) {
          e.preventDefault();
        }
      }
    });
  });

  // Basic client-side phone/email hinting (server still validates everything).
  document.querySelectorAll('input[type="tel"]').forEach(function (input) {
    input.addEventListener('input', function () {
      this.value = this.value.replace(/[^0-9+]/g, '');
    });
  });

  // ---- Mobile sidebar / off-canvas drawer ----
  var sidebar = document.getElementById('reflexSidebar');
  var toggleBtn = document.getElementById('sidebarToggle');
  var backdrop = document.getElementById('sidebarBackdrop');

  function openSidebar() {
    if (!sidebar) return;
    sidebar.classList.add('is-open');
    if (backdrop) backdrop.classList.add('is-visible');
    if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    if (!sidebar) return;
    sidebar.classList.remove('is-open');
    if (backdrop) backdrop.classList.remove('is-visible');
    if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }

  if (toggleBtn) {
    toggleBtn.addEventListener('click', function () {
      if (sidebar.classList.contains('is-open')) {
        closeSidebar();
      } else {
        openSidebar();
      }
    });
  }

  if (backdrop) {
    backdrop.addEventListener('click', closeSidebar);
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeSidebar();
  });

  // Close the drawer automatically after tapping a nav link on mobile.
  if (sidebar) {
    sidebar.querySelectorAll('.sidebar-link, .sidebar-logout').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.innerWidth < 992) closeSidebar();
      });
    });
  }

  // Keep drawer state sane if the viewport is resized past the breakpoint.
  window.addEventListener('resize', function () {
    if (window.innerWidth >= 992) closeSidebar();
  });
});
