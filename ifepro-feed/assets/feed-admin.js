/**
 * HTB Live Feed - Admin JavaScript
 * Handles: tabs, copy shortcode, clear cache AJAX, live preview (no pagination)
 */
jQuery(function ($) {
  'use strict';

  var i18n = ifeproFeedAdmin.i18n;

  // -------------------------------------------------------
  // Tabs
  // -------------------------------------------------------
  $('.ifeprofeed-tabs-nav a').on('click', function (e) {
    e.preventDefault();
    var target = $(this).attr('href');
    $('.ifeprofeed-tabs-nav a').removeClass('active');
    $(this).addClass('active');
    $('.ifeprofeed-tab-content').removeClass('active');
    $(target).addClass('active');
  });

  // -------------------------------------------------------
  // Source type radio: show/hide relevant ID fields
  // -------------------------------------------------------
  function syncSourceRows() {
    var val = $('input[name="_ifeprofeed_source_type"]:checked').val();
    $('.ifeprofeed-source-row').hide();
    $('.ifeprofeed-source-' + val).show();
  }

  $('input.ifeprofeed-source-type-radio').on('change', syncSourceRows);
  syncSourceRows(); // init

  // -------------------------------------------------------
  // Time filter: show/hide custom date rows
  // -------------------------------------------------------
  function syncTimeRows() {
    var val = $('select[name="_ifeprofeed_time_filter"]').val();
    $('.ifeprofeed-time-row').hide();
    if (val === 'custom') {
      $('.ifeprofeed-time-custom').show();
    }
  }

  $('select[name="_ifeprofeed_time_filter"]').on('change', syncTimeRows);
  syncTimeRows(); // init

  // Datepicker init
  $('.ifeprofeed-datepicker').datepicker({
    dateFormat: 'yy-mm-dd'
  });

  // -------------------------------------------------------
  // Layout picker: add active class on select
  // -------------------------------------------------------
  $(document).on('click', '.ifeprofeed-layout-option.ifeprofeed-layout-pro-only', function (e) {
    e.preventDefault();
    e.stopPropagation();
    return false;
  });

  $('.ifeprofeed-layout-option input[type="radio"]').on('change', function () {
    $('.ifeprofeed-layout-option').removeClass('active');
    $(this).closest('.ifeprofeed-layout-option').addClass('active');
  });

  // -------------------------------------------------------
  // Copy shortcode (meta box sidebar)
  // -------------------------------------------------------
  $('#ifeprofeed-copy-shortcode-btn').on('click', function () {
    var input = document.getElementById('ifeprofeed-shortcode-input');
    input.select();
    input.setSelectionRange(0, 99999);
    try {
      document.execCommand('copy');
      $(this).text(i18n.copied).prop('disabled', true);
    } catch (e) {
      navigator.clipboard && navigator.clipboard.writeText(input.value);
      $(this).text(i18n.copied).prop('disabled', true);
    }
  });

  // Copy shortcode in list table (click code element)
  $(document).on('click', '.ifeprofeed-copy-shortcode', function () {
    var sc   = $(this).data('shortcode');
    var $msg = $(this).next('.ifeprofeed-copied');
    try {
      navigator.clipboard.writeText(sc).then(function () {
        $msg.fadeIn(200).delay(1500).fadeOut(400);
      });
    } catch (e) {
      var ta = document.createElement('textarea');
      ta.value = sc;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      $msg.fadeIn(200).delay(1500).fadeOut(400);
    }
  });

  // -------------------------------------------------------
  // Clear cache AJAX (meta box Cache tab)
  // -------------------------------------------------------
  $('#ifeprofeed-clear-cache-btn').on('click', function () {
    var $btn = $(this);
    var $msg = $('#ifeprofeed-clear-cache-msg');
    var feedId = $btn.data('feed-id');
    var nonce  = $btn.data('nonce');

    $btn.prop('disabled', true).text(i18n.clearing);
    $msg.hide().removeClass('success error');

    $.post(ifeproFeedAdmin.ajax_url, {
      action:  'ifeprofeed_clear_cache',
      feed_id: feedId,
      nonce:   nonce
    })
    .done(function (res) {
      if (res.success) {
        $msg.addClass('success').text(i18n.cache_cleared).show();
      } else {
        $msg.addClass('error').text(res.data.message || i18n.cache_error).show();
      }
    })
    .fail(function () {
      $msg.addClass('error').text(i18n.cache_error).show();
    })
    .always(function () {
      $btn.prop('disabled', false).text('Clear Cache Now');
    });
  });

  // -------------------------------------------------------
  // Clear hard cache AJAX (meta box Settings tab)
  // -------------------------------------------------------
  $('#ifeprofeed-clear-hard-cache-btn').on('click', function () {
    var $btn = $(this);
    var $msg = $('#ifeprofeed-clear-hard-cache-msg');
    var feedId = $btn.data('feed-id');
    var nonce  = $btn.data('nonce');

    if (!confirm('Are you sure you want to clear the hard cache? This will delete all cached event cover images.')) {
      return;
    }

    $btn.prop('disabled', true).text(i18n.clearing);
    $msg.hide().removeClass('success error');

    $.post(ifeproFeedAdmin.ajax_url, {
      action:  'ifeprofeed_clear_hard_cache',
      feed_id: feedId,
      nonce:   nonce
    })
    .done(function (res) {
      if (res.success) {
        $msg.addClass('success').text(i18n.hard_cleared || 'Hard cache cleared!').show();
        // Update count text if present
        $btn.siblings('.description').first().html('<span style="color:#aaa;">&#9679; No HQ images cached yet.</span>');
      } else {
        $msg.addClass('error').text(res.data.message || i18n.cache_error).show();
      }
    })
    .fail(function () {
      $msg.addClass('error').text(i18n.cache_error).show();
    })
    .always(function () {
      $btn.prop('disabled', false).text('🗑 Clear Hard Cache (Images)');
    });
  });

  // -------------------------------------------------------
  // Cache duration: show/hide custom minutes input
  // -------------------------------------------------------
  $('input.ifeprofeed-cache-preset').on('change', function () {
    var val = $(this).val();
    if (val === 'custom') {
      $('.ifeprofeed-cache-custom-wrap').show().find('input').focus();
    } else {
      $('.ifeprofeed-cache-custom-wrap').hide();
    }
  });

  // -------------------------------------------------------
  // Live Preview Update (layout sample only — no pagination)
  // -------------------------------------------------------
  var previewTimeout = null;

  function updateLivePreview() {
    var $container1 = $('#ifeprofeed-preview-container');
    var $container2 = $('#ifepro-builder-preview-container');
    var $containers = $container1.add($container2);

    if (!$containers.length) {
      return;
    }

    var isFullPreview = $('.ifepro-builder__workspace').hasClass('is-full-preview');
    var $loading = $('.ifeprofeed-preview-loading');

    $loading.show();

    var formData = $('form#post').serializeArray();
    var feedId = $('#post_ID').val() || 0;

    formData.push({ name: 'action', value: 'ifeprofeed_live_preview' });
    formData.push({ name: 'feed_id', value: feedId });
    formData.push({ name: 'is_full_preview', value: isFullPreview ? 'true' : 'false' });

    $.post(ifeproFeedAdmin.ajax_url, formData)
      .done(function (res) {
        var $globalWarning = $('#ifepro-builder-global-warning');
        
        if (res.success && res.data.html) {
          $containers.html(res.data.html);
          $globalWarning.hide();

          if (res.data.warning) {
            $('.ifeprofeed-auto-dismiss-notice').remove();
            var warningNotice = $('<div class="notice notice-warning is-dismissible ifeprofeed-auto-dismiss-notice" style="margin: 10px 0; padding: 10px 15px; border-left: 4px solid #f59e0b; background: #fffbebf0; color: #92400e; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); font-size: 13px;"><p style="margin:0;"><strong>Notice:</strong> ' + res.data.warning + '</p></div>');
            $containers.prepend(warningNotice);
            setTimeout(function() {
              warningNotice.fadeOut(500, function() { $(this).remove(); });
            }, 5000);
          }

          $containers.find('img').each(function () {
            if (this.complete) {
              this.style.opacity = 1;
              var prev = this.previousElementSibling;
              if (prev && prev.classList.contains('ifeprofeed-skeleton')) {
                prev.style.display = 'none';
              }
            }
          });
          
          // Init Masonry if layout is masonry
          $containers.find('.ifeprofeed-layout-masonry').each(function() {
            var feedWrap = this;
            if (typeof Masonry !== 'undefined' && typeof imagesLoaded !== 'undefined') {
              var eventsContainer = feedWrap.querySelector('.ifeprofeed-events-grid');
              if (eventsContainer) {
                var gapStr = getComputedStyle(feedWrap).getPropertyValue('--ifeprofeed-gap').trim();
                var gap = parseInt(gapStr, 10) || 20;

                var msnry = new Masonry(eventsContainer, {
                  itemSelector: '.ifeprofeed-event-card',
                  percentPosition: true,
                  gutter: gap
                });
                imagesLoaded(eventsContainer, function() {
                  msnry.layout();
                });
              }
            }
          });
        } else {
          var errorMsg = res.data.message || 'Error loading preview';
          var errHtml = '<div class="ifeprofeed-preview-error"><p>' + errorMsg + '</p></div>';
          $containers.html(errHtml);
          
          if (errorMsg.indexOf("This content isn't available") !== -1 || errorMsg.indexOf("private") !== -1 || errorMsg.indexOf("restricted") !== -1) {
            var sourceType = $('input[name="_ifeprofeed_source_type"]:checked').val();
            var warningText = 'We cannot fetch data because the Facebook Page/Event might be private or country-restricted. Please change the Page ID or check settings.';
            if (sourceType === 'group_id') {
              warningText = 'We cannot fetch data because the Facebook Group might be private or country-restricted. Please change the Group ID or check settings.';
            }
            $globalWarning.find('.ifepro-warning-text').text(warningText);
            $globalWarning.show();
          } else {
            $globalWarning.hide();
          }
        }
      })
      .fail(function () {
        var failHtml = '<div class="ifeprofeed-preview-error"><p>Failed to contact server for preview.</p></div>';
        $containers.html(failHtml);
      })
      .always(function () {
        $loading.hide();
      });
  }

  function triggerPreviewUpdate() {
    clearTimeout(previewTimeout);
    previewTimeout = setTimeout(updateLivePreview, 400);
  }

  $(document).on('change input click blur',
    '#ifeprofeed-tab-source input, #ifeprofeed-tab-source textarea, #ifeprofeed-tab-source select, ' +
    '#ifepro-panel-body-source input, #ifepro-panel-body-source textarea, #ifepro-panel-body-source select, ' +
    '#ifeprofeed-tab-display input, #ifeprofeed-tab-display textarea, #ifeprofeed-tab-display select, ' +
    '#ifeprofeed-tab-tickets input, #ifeprofeed-tab-tickets textarea, #ifeprofeed-tab-tickets select, ' +
    '#ifeprofeed-tab-filters input, #ifeprofeed-tab-filters textarea, #ifeprofeed-tab-filters select, ' +
    '#ifepro-panel-body-display input, #ifepro-panel-body-display textarea, #ifepro-panel-body-display select, ' +
    '#ifepro-panel-body-tickets input, #ifepro-panel-body-tickets textarea, #ifepro-panel-body-tickets select, ' +
    '#ifepro-panel-body-filters input, #ifepro-panel-body-filters textarea, #ifepro-panel-body-filters select, ' +
    '.ifeprofeed-layout-option:not(.ifeprofeed-layout-pro-only)',
    triggerPreviewUpdate
  );

  setTimeout(updateLivePreview, 500);

  // -------------------------------------------------------
  // Toggle Full Preview mode
  // -------------------------------------------------------
  $(document).on('click', '#ifepro-builder-toggle-full-preview', function (e) {
    e.preventDefault();
    var $workspace = $('.ifepro-builder__workspace');
    var $builder = $('#ifepro-builder');
    var $btn = $(this);
    var $iconWrap = $btn.find('.ifepro-preview-icon-wrap');
    var $text = $btn.find('.btn-text');

    if ($workspace.hasClass('is-full-preview')) {
      $workspace.removeClass('is-full-preview');
      $builder.removeClass('is-full-preview');
      $iconWrap.html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>');
      $text.text('Full Preview');
    } else {
      $workspace.addClass('is-full-preview');
      $builder.addClass('is-full-preview');
      $iconWrap.html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 14 10 14 10 20"></polyline><polyline points="20 10 14 10 14 4"></polyline><line x1="14" y1="10" x2="21" y2="3"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>');
      $text.text('Close Preview');
    }

    updateLivePreview();
  });

  // -------------------------------------------------------
  // Hover to preview Mockup for Layouts
  // -------------------------------------------------------
  $(document).on('mouseenter', '.ifeprofeed-layout-option', function() {
    var $layoutOpt = $(this);
    
    // Only show hover mockup for PRO layouts that are locked
    if (!$layoutOpt.hasClass('ifeprofeed-layout-pro-only')) {
      return;
    }
    
    var layout = $layoutOpt.find('input[type="radio"]').val();

    var $container1 = $('#ifeprofeed-preview-container').parent(); // .ifeprofeed-preview-body
    var $container2 = $('#ifepro-builder-preview-container').parent(); // .ifepro-builder__preview-body
    var $parents = $container1.add($container2);

    $parents.each(function() {
      var $p = $(this);
      if ($p.find('.ifeprofeed-hover-overlay').length === 0) {
        $p.prepend('<div class="ifeprofeed-hover-overlay" style="display:none; background:#f8fafc; border: 1px solid #e2e8f0; padding:20px; border-radius:8px; box-sizing:border-box;"></div>');
      }
    });

    var title = '';
    var html = '';

    if (layout === 'masonry') {
      title = 'Masonry Preview (PRO)';
      html = `<div style="display: flex; gap: 12px; align-items: flex-start;">
                <div style="flex: 1; display: flex; flex-direction: column; gap: 12px;">
                    <div style="background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); overflow:hidden; border: 1px solid #f1f5f9;">
                        <div style="height: 150px; background: #e2e8f0;"></div>
                        <div style="padding: 12px;">
                            <div style="width: 80%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                            <div style="width: 50%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                        </div>
                    </div>
                    <div style="background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); overflow:hidden; border: 1px solid #f1f5f9;">
                        <div style="height: 100px; background: #e2e8f0;"></div>
                        <div style="padding: 12px;">
                            <div style="width: 90%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                            <div style="width: 40%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                        </div>
                    </div>
                </div>
                <div style="flex: 1; display: flex; flex-direction: column; gap: 12px;">
                    <div style="background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); overflow:hidden; border: 1px solid #f1f5f9;">
                        <div style="height: 100px; background: #e2e8f0;"></div>
                        <div style="padding: 12px;">
                            <div style="width: 70%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                            <div style="width: 60%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                        </div>
                    </div>
                    <div style="background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); overflow:hidden; border: 1px solid #f1f5f9;">
                        <div style="height: 160px; background: #e2e8f0;"></div>
                        <div style="padding: 12px;">
                            <div style="width: 75%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                            <div style="width: 55%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                        </div>
                    </div>
                </div>
            </div>`;
    } else if (layout === 'minimal-grid') {
      title = 'Minimal Grid Preview (PRO)';
      html = `<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                <div style="background: #fff; border-radius: 4px; border: 1px solid #e2e8f0; padding: 20px; min-height: 160px; display: flex; flex-direction: column; justify-content: center;">
                    <div style="width: 100%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 16px;"></div>
                    <div style="width: 60%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 24px;"></div>
                    <div style="width: 40%; height: 6px; background: #cbd5e1; border-radius: 4px; margin-bottom: 10px;"></div>
                    <div style="width: 50%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                </div>
                <div style="background: #fff; border-radius: 4px; border: 1px solid #e2e8f0; padding: 20px; min-height: 160px; display: flex; flex-direction: column; justify-content: center;">
                    <div style="width: 80%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 16px;"></div>
                    <div style="width: 90%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 24px;"></div>
                    <div style="width: 50%; height: 6px; background: #cbd5e1; border-radius: 4px; margin-bottom: 10px;"></div>
                    <div style="width: 30%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                </div>
                <div style="background: #fff; border-radius: 4px; border: 1px solid #e2e8f0; padding: 20px; min-height: 160px; display: flex; flex-direction: column; justify-content: center;">
                    <div style="width: 90%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 16px;"></div>
                    <div style="width: 70%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 24px;"></div>
                    <div style="width: 60%; height: 6px; background: #cbd5e1; border-radius: 4px; margin-bottom: 10px;"></div>
                    <div style="width: 45%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                </div>
            </div>`;
    } else if (layout === 'compact-list') {
      title = 'Compact List Preview (PRO)';
      html = `<div style="display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; background: #fff; border-radius: 6px; padding: 10px; align-items: center; gap: 12px; border: 1px solid #f1f5f9;">
                    <div style="width: 40px; height: 40px; background: #e2e8f0; border-radius: 4px;"></div>
                    <div style="flex: 1;">
                        <div style="width: 60%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 6px;"></div>
                        <div style="width: 30%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                    </div>
                </div>
                <div style="display: flex; background: #fff; border-radius: 6px; padding: 10px; align-items: center; gap: 12px; border: 1px solid #f1f5f9;">
                    <div style="width: 40px; height: 40px; background: #e2e8f0; border-radius: 4px;"></div>
                    <div style="flex: 1;">
                        <div style="width: 75%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 6px;"></div>
                        <div style="width: 40%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                    </div>
                </div>
                <div style="display: flex; background: #fff; border-radius: 6px; padding: 10px; align-items: center; gap: 12px; border: 1px solid #f1f5f9;">
                    <div style="width: 40px; height: 40px; background: #e2e8f0; border-radius: 4px;"></div>
                    <div style="flex: 1;">
                        <div style="width: 50%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 6px;"></div>
                        <div style="width: 25%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                    </div>
                </div>
            </div>`;
    } else if (layout === 'timeline') {
      title = 'Timeline Preview (PRO)';
      html = `<div style="padding-left: 20px; border-left: 2px solid #e2e8f0; position: relative; margin-left: 10px;">
                <div style="margin-bottom: 20px; position: relative;">
                    <div style="position: absolute; left: -26px; top: 0; width: 10px; height: 10px; background: #94a3b8; border-radius: 50%; border: 2px solid #fff;"></div>
                    <div style="background: #fff; border-radius: 8px; border: 1px solid #f1f5f9; padding: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <div style="width: 70%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                        <div style="width: 40%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                    </div>
                </div>
                <div style="margin-bottom: 20px; position: relative;">
                    <div style="position: absolute; left: -26px; top: 0; width: 10px; height: 10px; background: #94a3b8; border-radius: 50%; border: 2px solid #fff;"></div>
                    <div style="background: #fff; border-radius: 8px; border: 1px solid #f1f5f9; padding: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <div style="width: 60%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                        <div style="width: 30%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                    </div>
                </div>
                <div style="position: relative;">
                    <div style="position: absolute; left: -26px; top: 0; width: 10px; height: 10px; background: #94a3b8; border-radius: 50%; border: 2px solid #fff;"></div>
                    <div style="background: #fff; border-radius: 8px; border: 1px solid #f1f5f9; padding: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <div style="width: 80%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                        <div style="width: 50%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                    </div>
                </div>
            </div>`;
    } else if (layout === 'ticket-list') {
      title = 'Ticket Preview (PRO)';
      html = `<div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #f1f5f9; position: relative; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                    <div style="width: 80px; background: #f1f5f9; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px;">
                        <div style="width: 30px; height: 6px; background: #cbd5e1; border-radius: 4px; margin-bottom: 6px;"></div>
                        <div style="width: 40px; height: 12px; background: #94a3b8; border-radius: 4px;"></div>
                    </div>
                    <div style="flex: 1; padding: 12px;">
                        <div style="width: 70%; height: 10px; background: #94a3b8; border-radius: 4px; margin-bottom: 12px;"></div>
                        <div style="width: 40%; height: 6px; background: #cbd5e1; border-radius: 4px; margin-bottom: 8px;"></div>
                        <div style="width: 80%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                    </div>
                    <div style="border-left: 2px dashed #e2e8f0; width: 80px; display: flex; align-items: center; justify-content: center; padding: 12px;">
                        <div style="width: 80%; height: 24px; background: #e2e8f0; border-radius: 4px;"></div>
                    </div>
                </div>
                <div style="display: flex; background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #f1f5f9; position: relative; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                    <div style="width: 80px; background: #f1f5f9; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px;">
                        <div style="width: 30px; height: 6px; background: #cbd5e1; border-radius: 4px; margin-bottom: 6px;"></div>
                        <div style="width: 40px; height: 12px; background: #94a3b8; border-radius: 4px;"></div>
                    </div>
                    <div style="flex: 1; padding: 12px;">
                        <div style="width: 80%; height: 10px; background: #94a3b8; border-radius: 4px; margin-bottom: 12px;"></div>
                        <div style="width: 30%; height: 6px; background: #cbd5e1; border-radius: 4px; margin-bottom: 8px;"></div>
                        <div style="width: 60%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                    </div>
                    <div style="border-left: 2px dashed #e2e8f0; width: 80px; display: flex; align-items: center; justify-content: center; padding: 12px;">
                        <div style="width: 80%; height: 24px; background: #e2e8f0; border-radius: 4px;"></div>
                    </div>
                </div>
            </div>`;
    }

    if (html !== '') {
      var headerHtml = '<h3 style="margin-top:0;font-size:13px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;margin-bottom:20px;">' + title + '</h3>';
      
      $parents.find('#ifeprofeed-preview-container, #ifepro-builder-preview-container').hide();
      $parents.find('.ifeprofeed-hover-overlay').html(headerHtml + html).stop(true, true).fadeIn(150);
    }
  }).on('mouseleave', '.ifeprofeed-layout-option', function() {
    var $container1 = $('#ifeprofeed-preview-container').parent();
    var $container2 = $('#ifepro-builder-preview-container').parent();
    var $parents = $container1.add($container2);
    
    $parents.find('.ifeprofeed-hover-overlay').stop(true, true).hide();
    $parents.find('#ifeprofeed-preview-container, #ifepro-builder-preview-container').fadeIn(150);
  });
});
