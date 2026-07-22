/**
 * HTB Live Feed - Public JavaScript
 * Vanilla JS only. Initializes Eventbrite ticket widgets + AJAX pagination.
 */
(function () {
  'use strict';

  var data = window.ifeproFeedData || {};
  var ticketStyle = data.ticket_style || 'link';
  var modalEventIds = data.modal_event_ids || [];

  // -------------------------------------------------------
  // Init on DOM ready
  // -------------------------------------------------------
  function ready(fn) {
    if (document.readyState !== 'loading') { fn(); }
    else { document.addEventListener('DOMContentLoaded', fn); }
  }

  ready(function () {
    if (ticketStyle === 'modal') { initModals(); }
    initFeedWraps();
  });

  // -------------------------------------------------------
  // Initialize Feed Wraps (Pagination + Masonry)
  // -------------------------------------------------------
  function initFeedWraps() {
    var feedWraps = document.querySelectorAll('.ifeprofeed-feed-wrap[data-feed-id]');
    
    feedWraps.forEach(function (feedWrap) {
      if (feedWrap.getAttribute('data-pagination-init')) return;
      feedWrap.setAttribute('data-pagination-init', 'true');

      initMasonry(feedWrap);
      bindPaginationEvents(feedWrap);
    });
  }

  function initMasonry(feedWrap) {
    if (!feedWrap.classList.contains('ifeprofeed-layout-masonry')) return;
    if (typeof Masonry === 'undefined' || typeof imagesLoaded === 'undefined') return;

    var eventsContainer = feedWrap.querySelector('[data-events-container]');
    if (!eventsContainer) return;

    var gapStr = getComputedStyle(feedWrap).getPropertyValue('--ifeprofeed-gap').trim();
    var gap = parseInt(gapStr, 10) || 20;

    var msnry = new Masonry(eventsContainer, {
      itemSelector: '.ifeprofeed-event-card',
      percentPosition: true,
      gutter: gap
    });
    feedWrap.ifeprofeedMsnry = msnry;

    imagesLoaded(eventsContainer, function() {
      msnry.layout();
    });
  }

  function bindPaginationEvents(feedWrap) {
    // Event delegation for pagination buttons
    feedWrap.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-page]');
      if (!btn) return;

      var page = parseInt(btn.getAttribute('data-page'), 10);
      if (!page || btn.disabled) return;

      var feedId = feedWrap.getAttribute('data-feed-id');
      var perPage = parseInt(feedWrap.getAttribute('data-per-page'), 10) || 12;
      var currentPage = parseInt(feedWrap.getAttribute('data-current-page'), 10) || 1;

      if (page === currentPage && feedWrap.getAttribute('data-pagination-type') !== 'load_more') return;

      loadPage(feedWrap, feedId, page, perPage);
    });

    // Init infinite scroll if needed
    if (feedWrap.getAttribute('data-pagination-type') === 'infinite_scroll') {
      initInfiniteScroll(feedWrap);
    }
  }

  // -------------------------------------------------------
  // Modal: eb_widgets checkout popup
  // -------------------------------------------------------
  function initModals() {
    if (!window.EBWidgets) {
      // eb_widgets.js might load after this script — retry
      var attempts = 0;
      var timer = setInterval(function () {
        attempts++;
        if (window.EBWidgets) { clearInterval(timer); setupModals(); }
        if (attempts > 20)    { clearInterval(timer); }
      }, 300);
      return;
    }
    setupModals();
  }

  function setupModals() {
    modalEventIds.forEach(function (eventId) {
      var btnId = 'ifeprofeed-ticket-btn-' + eventId;
      var btn   = document.getElementById(btnId);
      if (!btn) return;

      try {
        window.EBWidgets.createWidget({
          widgetType: 'checkout',
          eventId:    eventId,
          modal:      true,
          modalTriggerElementId: btnId,
          onOrderComplete: function () {
            // Optional: refresh badge state after purchase
            btn.textContent = btn.getAttribute('data-sold-label') || btn.textContent;
          }
        });
      } catch (e) {
        // Fallback: open Eventbrite URL directly
        btn.addEventListener('click', function () {
          var card = btn.closest('.ifeprofeed-event-card');
          var link = card ? card.querySelector('a[href]') : null;
          if (link) { window.open(link.href, '_blank', 'noopener'); }
        });
      }
    });
  }

  function loadPage(feedWrap, feedId, page, perPage) {
    if (feedWrap.getAttribute('data-loading') === 'true') return;

    var eventsContainer = feedWrap.querySelector('[data-events-container]');
    var paginationContainer = feedWrap.querySelector('[data-pagination]');

    if (!eventsContainer || !paginationContainer) return;

    // Show loading state
    feedWrap.classList.add('ifeprofeed-loading');
    feedWrap.setAttribute('data-loading', 'true');
    eventsContainer.style.opacity = '0.5';

    // Build form data
    var formData = new FormData();
    formData.append('action', 'ifeprofeed_load_page');
    formData.append('feed_id', feedId);
    formData.append('page', page);
    formData.append('per_page', perPage);

    // Send AJAX request
    fetch(data.ajaxUrl || '/wp-admin/admin-ajax.php', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(function (response) { return response.json(); })
    .then(function (result) {
      feedWrap.classList.remove('ifeprofeed-loading');
      feedWrap.removeAttribute('data-loading');
      eventsContainer.style.opacity = '1';

      if (result.success) {
        var paginationType = feedWrap.getAttribute('data-pagination-type') || 'ajax';

        if (paginationType === 'load_more' || paginationType === 'infinite_scroll') {
          // Append events instead of replacing
          var tempDiv = document.createElement('div');
          tempDiv.innerHTML = result.data.events_html;
          var newEvents = Array.prototype.slice.call(tempDiv.children);
          var appendedElements = [];
          
          while (newEvents.length > 0) {
            var el = newEvents.shift();
            eventsContainer.appendChild(el);
            appendedElements.push(el);
          }

          if (feedWrap.ifeprofeedMsnry) {
            feedWrap.ifeprofeedMsnry.appended(appendedElements);
            imagesLoaded(eventsContainer, function() {
              feedWrap.ifeprofeedMsnry.layout();
            });
          }
        } else {
          // Replace events (numbered pagination)
          eventsContainer.innerHTML = result.data.events_html;
          
          if (feedWrap.ifeprofeedMsnry) {
            feedWrap.ifeprofeedMsnry.reloadItems();
            imagesLoaded(eventsContainer, function() {
              feedWrap.ifeprofeedMsnry.layout();
            });
          }

          // Scroll to top of feed for numbered pagination
          feedWrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Update pagination
        paginationContainer.innerHTML = result.data.pagination_html;

        // Update current page attribute
        feedWrap.setAttribute('data-current-page', result.data.current_page);

        // Re-init ticket widgets
        if (ticketStyle === 'modal') {
          setTimeout(initModals, 100);
        }

        // Re-init infinite scroll observer if needed
        if (paginationType === 'infinite_scroll') {
          initInfiniteScroll(feedWrap);
        }
      } else {
        showError(eventsContainer, result.data?.message || 'Failed to load events.');
      }
    })
    .catch(function (err) {
      feedWrap.classList.remove('ifeprofeed-loading');
      feedWrap.removeAttribute('data-loading');
      eventsContainer.style.opacity = '1';
      showError(eventsContainer, 'Network error. Please try again.');
    });
  }

  function showError(container, message) {
    var errorDiv = document.createElement('div');
    errorDiv.className = 'ifeprofeed-feed-error';
    errorDiv.textContent = message;
    container.appendChild(errorDiv);
    setTimeout(function () {
      errorDiv.remove();
    }, 3000);
  }

  // -------------------------------------------------------
  // Infinite Scroll (Intersection Observer)
  // -------------------------------------------------------
  function initInfiniteScroll(feedWrap) {
    if (!feedWrap) return;

    var sentinel = feedWrap.querySelector('.ifeprofeed-infinite-sentinel');
    if (!sentinel) return;

    var currentPage = parseInt(feedWrap.getAttribute('data-current-page'), 10) || 1;
    var totalPages = parseInt(feedWrap.getAttribute('data-total-pages'), 10) || 1;

    if (currentPage >= totalPages) {
        sentinel.style.display = 'none';
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting && !feedWrap.classList.contains('ifeprofeed-loading')) {
          var nextPage = parseInt(feedWrap.getAttribute('data-current-page'), 10) + 1;
          var feedId = feedWrap.getAttribute('data-feed-id');
          var perPage = parseInt(feedWrap.getAttribute('data-per-page'), 10) || 12;

          loadPage(feedWrap, feedId, nextPage, perPage);
          
          // Unobserve old sentinel as it will be replaced
          observer.unobserve(sentinel);
        }
      });
    }, {
      rootMargin: '100px',
      threshold: 0.1
    });

    observer.observe(sentinel);
  }

  // Initialize on load
  ready(function () {
    if (ticketStyle === 'modal') { initModals(); }
    initFeedWraps();
  });

})();
