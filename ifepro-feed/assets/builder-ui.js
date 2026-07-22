/**
 * IME Pro Live Feed – Builder UI Controller
 *
 * Moves existing metabox fields into multi-step builder panels.
 * The custom Save button triggers the hidden native #publish button.
 * No new save logic — all name="" attributes stay unchanged.
 */
jQuery(function ($) {
  'use strict';

  // Bail if builder shell was not injected.
  if (!$('#ifepro-builder').length) return;

  var config    = window.ifeproBuilderUI || {};
  var i18n      = config.i18n || {};
  var $builder  = $('#ifepro-builder');
  var $panels   = $builder.find('.ifepro-builder__panel');
  var $steps    = $builder.find('.ifepro-builder__step-indicator');
  var $prevBtn  = $('#ifepro-builder-prev');
  var $nextBtn  = $('#ifepro-builder-next');
  var $saveBtn  = $('#ifepro-builder-save');
  var $counter  = $('#ifepro-builder-counter');
  var total     = $panels.length;
  var current   = 0;

  // ─────────────────────────────────────────────────────────
  // 1. MOVE TITLE INTO BUILDER
  // ─────────────────────────────────────────────────────────
  (function moveTitle() {
    var $title = $('#title');
    if ($title.length) {
      $title.attr('placeholder', i18n.titlePlh || 'Enter feed name…');
      $('#ifepro-builder-title-slot').append($title);
    }
    // Also move title-prompt-text if it exists
    $('#title-prompt-text').remove();
  })();

  // ─────────────────────────────────────────────────────────
  // 2. MOVE METABOX FIELDS INTO BUILDER PANELS
  // ─────────────────────────────────────────────────────────
  (function moveFields() {
    // Step 1: Source — content of #ifeprofeed-tab-source
    moveTabContent('#ifeprofeed-tab-source', '#ifepro-panel-body-source');

    // Step 2: Filters — content of #ifeprofeed-tab-filters
    moveTabContent('#ifeprofeed-tab-filters', '#ifepro-panel-body-filters');

    // Step 3: Display — content of #ifeprofeed-tab-display
    moveTabContent('#ifeprofeed-tab-display', '#ifepro-panel-body-display');

    // Step 4: Tickets — content of #ifeprofeed-tab-tickets
    moveTabContent('#ifeprofeed-tab-tickets', '#ifepro-panel-body-tickets');

    // Step 5: Settings — content of #ifeprofeed-tab-settings
    moveTabContent('#ifeprofeed-tab-settings', '#ifepro-panel-body-settings');

    // Move nonce field into builder so it submits with the form
    var $nonce = $('input[name="ifeprofeed_nonce"]');
    if ($nonce.length) {
      $builder.append($nonce);
    }
  })();

  /**
   * Move the inner content of a metabox tab into a builder panel body.
   */
  function moveTabContent(sourceSelector, targetSelector) {
    var $source = $(sourceSelector);
    var $target = $(targetSelector);
    if ($source.length && $target.length) {
      $source.children().appendTo($target);
    }
  }

  // ─────────────────────────────────────────────────────────
  // 3. STEP NAVIGATION & VALIDATION
  // ─────────────────────────────────────────────────────────

  function validateStep0() {
    var isValid = true;
    var $firstError = null;

    // Reset previous errors
    $('.ifepro-error-msg').remove();
    $('.ifepro-builder__panel-body input.ifepro-error-field, #title.ifepro-error-field').removeClass('ifepro-error-field');

    function showError($el, msg) {
      $el.addClass('ifepro-error-field');
      $el.after('<div class="ifepro-error-msg" style="color:#d63638;font-size:12px;margin-top:4px;">' + msg + '</div>');
      isValid = false;
      if (!$firstError) $firstError = $el;
    }

    // Title validation
    var $title = $('#title');
    if (!($title.val() || '').trim()) {
      showError($title, i18n.reqTitle || 'Widget name is required.');
    }

    // Source validation
    var sourceType = $('input[name="_ifeprofeed_source_type"]:checked').val();
    if (sourceType === 'page_id') {
      var $pageId = $('[name="_ifeprofeed_page_id"]');
      if (!($pageId.val() || '').trim()) showError($pageId, i18n.reqPageId || 'Facebook Page ID or Slug is required.');
    } else if (sourceType === 'group_id') {
      var $groupId = $('[name="_ifeprofeed_group_id"]');
      if (!($groupId.val() || '').trim()) showError($groupId, i18n.reqGroupId || 'Facebook Group URL or ID is required.');
    } else if (sourceType === 'event_ids') {
      var $eventIds = $('[name="_ifeprofeed_event_ids"]');
      if (!($eventIds.val() || '').trim()) showError($eventIds, i18n.reqEventIds || 'At least one Event ID is required.');
    } else if (sourceType === 'ical_url') {
      var $icalUrl = $('[name="_ifeprofeed_ical_url"]');
      if (!($icalUrl.val() || '').trim()) showError($icalUrl, i18n.reqIcalUrl || 'iCal URL is required.');
    }

    if (!isValid && $firstError) {
      $firstError.focus();
    }

    return isValid;
  }

  function goToStep(index) {
    if (index < 0 || index >= total) return;

    // Validate Step 1 if moving forward or jumping from Step 1
    if (current === 0 && index > 0) {
      if (!validateStep0()) {
        return; // Stop navigation
      }
    }

    current = index;

    // Update panels
    $panels.removeClass('is-active');
    $panels.eq(current).addClass('is-active');

    // Update stepper indicators
    $steps.each(function (i) {
      var $s = $(this);
      $s.removeClass('is-active is-completed');
      if (i < current) {
        $s.addClass('is-completed');
      } else if (i === current) {
        $s.addClass('is-active');
      }
    });

    // Update buttons
    $prevBtn.css('visibility', current === 0 ? 'hidden' : 'visible');

    if (current === total - 1) {
      $nextBtn.hide();
    } else {
      $nextBtn.show();
    }

    // Update counter
    $counter.html(
      (i18n.step_of || 'Step %1$s of %2$s')
        .replace('%1$s', '<strong>' + (current + 1) + '</strong>')
        .replace('%2$s', '<strong>' + total + '</strong>')
    );

    // Scroll to top of builder
    $('html, body').animate({ scrollTop: $builder.offset().top - 20 }, 200);
  }

  // Button click handlers
  $nextBtn.on('click', function () {
    goToStep(current + 1);
  });

  $prevBtn.on('click', function () {
    goToStep(current - 1);
  });

  // Click on step indicator to jump
  $steps.on('click', function () {
    var idx = parseInt($(this).attr('data-step'), 10);
    // Don't validate if going backwards to step 0, only going forwards from step 0
    if (current === 0 && idx > 0 && !validateStep0()) {
      return;
    }
    goToStep(idx);
  });

  // ─────────────────────────────────────────────────────────
  // 4. SAVE BUTTON — triggers native WP #publish
  // ─────────────────────────────────────────────────────────
  $saveBtn.on('click', function () {
    // Always validate before saving
    if (!validateStep0()) {
      // If validation fails, jump back to Step 1 to show errors
      if (current !== 0) {
        goToStep(0);
      }
      return;
    }

    var $publishBtn = $('#publish');
    if ($publishBtn.length) {
      $saveBtn.prop('disabled', true).html(
        '<span class="dashicons dashicons-update ifepro-spin"></span> ' + (i18n.saving || 'Saving…')
      );
      $publishBtn.trigger('click');
    }
  });

  // ─────────────────────────────────────────────────────────
  // 5. COPY SHORTCODE (top bar)
  // ─────────────────────────────────────────────────────────
  $('#ifepro-builder-copy-sc').on('click', function () {
    var $code = $('#ifepro-builder-shortcode');
    var text  = $code.text().trim();
    var $btn  = $(this);
    var $iconWrap = $btn.find('.ifepro-copy-icon-wrap');
    
    var clipboardSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>';
    var checkSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';

    navigator.clipboard.writeText(text).then(function () {
      $btn.addClass('is-copied');
      $iconWrap.html(checkSvg);
      setTimeout(function () {
        $btn.removeClass('is-copied');
        $iconWrap.html(clipboardSvg);
      }, 2000);
    }).catch(function () {
      // Fallback
      var ta = document.createElement('textarea');
      ta.value = text;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
    });
  });

  // ─────────────────────────────────────────────────────────
  // 6. KEYBOARD NAV
  // ─────────────────────────────────────────────────────────
  $(document).on('keydown', function (e) {
    // Don't capture when typing in inputs
    if ($(e.target).is('input, textarea, select')) return;

    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
      e.preventDefault();
      goToStep(current + 1);
    } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
      e.preventDefault();
      goToStep(current - 1);
    }
  });

  // ─────────────────────────────────────────────────────────
  // 7. INIT — set first step
  // ─────────────────────────────────────────────────────────
  goToStep(0);
});
