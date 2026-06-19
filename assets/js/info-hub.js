/**
 * maca Njuvs frontend helpers.
 */
(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('.maca-info-event-booking-trigger');
        if (!trigger) {
            return;
        }

        event.preventDefault();

        var shell = document.querySelector('.maca-info-events-booking-shell .maca-table-booking');
        if (!shell) {
            return;
        }

        if (typeof window.macaMenulistInitTableBooking === 'function') {
            window.macaMenulistInitTableBooking(shell);
        }

        var bookingDate = trigger.getAttribute('data-booking-date') || '';
        var dateInput = shell.querySelector('[name="booking_date"]');
        if (dateInput && bookingDate) {
            dateInput.value = bookingDate;
            dateInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        var modal = shell.querySelector('.maca-table-booking-modal');
        if (modal) {
            modal.hidden = false;
            document.body.classList.add('maca-booking-modal-open');
            return;
        }

        var openTrigger = shell.querySelector('.maca-table-booking-trigger');
        if (openTrigger) {
            openTrigger.click();
        }
    });

    function scrollToEventDetail() {
        var hash = window.location.hash;

        if (!hash || hash.charAt(0) !== '#') {
            return;
        }

        var target = document.querySelector(hash);

        if (target && target.classList.contains('maca-info-event-detail--collapsible')) {
            target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scrollToEventDetail);
    } else {
        scrollToEventDetail();
    }

    window.addEventListener('hashchange', scrollToEventDetail);

    function restartBannerTicker(banner) {
        if (!banner || !banner.classList.contains('maca-info-news-list--scroll')) {
            return;
        }

        var inner = banner.querySelector('.maca-info-news-banner-track-inner');

        if (!inner) {
            return;
        }

        inner.style.animation = 'none';
        void inner.offsetHeight;
        inner.style.animation = '';
    }

    function getAdminBarHeight() {
        var adminBar = document.getElementById('wpadminbar');

        return adminBar ? adminBar.offsetHeight : 0;
    }

    function getNewsBannerHeight() {
        var banner = document.querySelector('.maca-info-news-list--banner:not(.maca-info-news-list--preview)');

        return banner ? banner.offsetHeight : 0;
    }

    function isSidebarNodeExcluded(sidebar, node) {
        if (!sidebar || !node || node === sidebar) {
            return true;
        }

        return sidebar.contains(node) || node.contains(sidebar);
    }

    function getPageContentTop(sidebar) {
        var selectors = [
            'main',
            '#primary',
            '.site-main',
            '#content',
            '.site-content',
            '.entry-content',
            'article',
            '.wp-block-post-content'
        ];
        var minTop = getAdminBarHeight() + getNewsBannerHeight() + 16;
        var best = null;

        selectors.forEach(function (selector) {
            document.querySelectorAll(selector).forEach(function (node) {
                if (isSidebarNodeExcluded(sidebar, node)) {
                    return;
                }

                var rect = node.getBoundingClientRect();

                if (rect.height < 40) {
                    return;
                }

                var absoluteTop = rect.top + window.scrollY;

                if (best === null || absoluteTop < best) {
                    best = absoluteTop;
                }
            });
        });

        if (best !== null) {
            return Math.max(minTop, best);
        }

        var header = document.querySelector('#masthead, .site-header, header[role="banner"]');

        if (header && !isSidebarNodeExcluded(sidebar, header)) {
            var headerRect = header.getBoundingClientRect();

            return Math.max(minTop, headerRect.bottom + window.scrollY + 8);
        }

        return minTop;
    }

    function setSidebarPosition(sidebar) {
        if (!window.matchMedia('(min-width: 783px)').matches) {
            document.documentElement.style.removeProperty('--maca-info-news-sidebar-top');
            return;
        }

        var contentTop = getPageContentTop(sidebar);
        var minTop = getAdminBarHeight() + getNewsBannerHeight() + 16;
        var top = Math.max(minTop, contentTop - window.scrollY);

        document.documentElement.style.setProperty('--maca-info-news-sidebar-top', top + 'px');
    }

    function applyNewsLayoutChrome() {
        var banner = document.querySelector('.maca-info-news-list--banner:not(.maca-info-news-list--preview)');

        if (banner) {
            if (!banner.querySelector('.maca-info-news-item')) {
                return;
            }

            if (banner.parentNode !== document.body) {
                document.body.insertBefore(banner, document.body.firstChild);
            }

            document.body.classList.add('maca-has-info-news-banner');

            var setBannerHeight = function () {
                var node = document.querySelector('.maca-info-news-list--banner:not(.maca-info-news-list--preview)');
                if (!node) {
                    return;
                }
                document.body.style.setProperty('--maca-info-news-banner-height', node.offsetHeight + 'px');
                restartBannerTicker(node);
            };

            setBannerHeight();
            window.addEventListener('resize', setBannerHeight);
        }

        var sidebar = document.querySelector('.maca-info-news-list--sidebar-left:not(.maca-info-news-list--preview), .maca-info-news-list--sidebar-right:not(.maca-info-news-list--preview)');

        if (sidebar) {
            if (sidebar.parentNode !== document.body) {
                document.body.appendChild(sidebar);
            }

            if (sidebar.classList.contains('maca-info-news-list--sidebar-left')) {
                document.body.classList.add('maca-has-info-news-sidebar-left');
            }

            if (sidebar.classList.contains('maca-info-news-list--sidebar-right')) {
                document.body.classList.add('maca-has-info-news-sidebar-right');
            }

            var sidebarWidth = getComputedStyle(sidebar).getPropertyValue('--maca-info-news-sidebar-width').trim() || '17.5rem';
            document.body.style.setProperty('--maca-info-news-sidebar-width', sidebarWidth);

            var setSidebarChrome = function () {
                var isDesktop = window.matchMedia('(min-width: 783px)').matches;

                if (isDesktop) {
                    setSidebarPosition(sidebar);
                    document.body.style.setProperty('--maca-info-news-sidebar-height', sidebar.offsetHeight + 'px');
                } else {
                    document.documentElement.style.removeProperty('--maca-info-news-sidebar-top');
                    document.body.style.removeProperty('--maca-info-news-sidebar-height');
                }
            };

            setSidebarChrome();
            window.addEventListener('resize', setSidebarChrome);
            window.addEventListener('scroll', setSidebarChrome, { passive: true });
            window.addEventListener('load', setSidebarChrome);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyNewsLayoutChrome);
    } else {
        applyNewsLayoutChrome();
    }

    document.addEventListener('click', function (event) {
        var modalTrigger = event.target.closest('.maca-info-news-modal-trigger');

        if (!modalTrigger) {
            return;
        }

        var modalId = modalTrigger.getAttribute('data-news-modal') || '';
        var dialog = modalId ? document.getElementById(modalId) : null;

        if (!dialog) {
            return;
        }

        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        } else {
            dialog.setAttribute('open', 'open');
        }
    });
})();
