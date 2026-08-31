/*
 * Admin Panel — হালকা vanilla JS
 * সাইডবার টগল (desktop collapse + mobile off-canvas), সাবমেনু টগল,
 * এবং ছোট UI ইন্টারঅ্যাকশন। লাইব্রেরি-মুক্ত।
 */
(function () {
    'use strict';

    const body = document.body;

    // ---------- সাইডবার collapse / off-canvas ----------
    const sidebar = document.getElementById('admin-sidebar');
    const sidebarToggle = document.getElementById('admin-sidebar-toggle');
    const sidebarToggleMobile = document.getElementById('admin-sidebar-toggle-mobile');
    const sidebarOverlay = document.getElementById('admin-sidebar-overlay');
    const sidebarCollapser = document.getElementById('admin-sidebar-collapser');

    if (sidebar) {
        // Desktop: collapse (টগল .is-collapsed)
        if (sidebarCollapser) {
            sidebarCollapser.addEventListener('click', () => {
                body.classList.toggle('admin-sidebar-collapsed');
                body.classList.remove('admin-mobile-open');
                try { localStorage.setItem('admin-sidebar-collapsed', body.classList.contains('admin-sidebar-collapsed') ? '1' : '0'); } catch (e) {}
            });
        }

        if (sidebarToggleMobile) {
            sidebarToggleMobile.addEventListener('click', (e) => {
                e.preventDefault();
                body.classList.toggle('admin-mobile-open');
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                body.classList.remove('admin-mobile-open');
            });
        }
    }

    // Restore desktop collapse preference
    try {
        if (localStorage.getItem('admin-sidebar-collapsed') === '1') {
            body.classList.add('admin-sidebar-collapsed');
        }
    } catch (e) {}

    // ---------- ড্রপডাউন সাবমেনু (sidebar group) ----------
    document.querySelectorAll('[data-admin-submenu-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            const group = toggle.parentElement;
            const isOpen = group.classList.contains('open');
            document.querySelectorAll('.admin-nav-group.open').forEach((g) => g.classList.remove('open'));
            if (! isOpen) group.classList.add('open');
        });
    });

    // ---------- অটো-হাইড সক্রিয় টোস্ট ----------
    window.setTimeout(() => {
        document.querySelectorAll('.admin-alert-auto').forEach((el) => {
            const alert = window.bootstrap ? window.bootstrap.Alert.getOrCreateInstance(el) : null;
            if (alert) { window.setTimeout(() => alert.close(), 3500); }
        });
    }, 300);

    // ---------- confirm dialog helper ----------
    document.querySelectorAll('[data-admin-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (! window.confirm(form.dataset.adminConfirm || 'আপনি কি নিশ্চিত?')) {
                e.preventDefault();
            }
        });
    });
})();
