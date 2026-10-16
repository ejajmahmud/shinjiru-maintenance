(function($) {
  'use strict';

  $(document).ready(function() {
    // Mode card radio toggle active class and dynamic mode settings visibility
    $('.shinjiru-mode-card input[type="radio"]').on('change', function() {
      var selectedMode = $(this).val();
      $('.shinjiru-mode-card').removeClass('is-selected');
      $(this).closest('.shinjiru-mode-card').addClass('is-selected');

      // Toggle dynamic mode settings blocks
      $('.shinjiru-mode-settings-block').hide();
      $('#shinjiru-mode-block-' + selectedMode).fadeIn(150);
    });

    // Template selection radio toggle
    $('.shinjiru-tpl-select-btn').on('click', function() {
      var card = $(this).closest('.shinjiru-tpl-card');
      $('.shinjiru-tpl-card').removeClass('is-active');
      $('.shinjiru-tpl-select-btn').removeClass('button-primary').addClass('button-secondary');
      $('.shinjiru-select-label').text('Use This Template');
      
      card.addClass('is-active');
      $(this).removeClass('button-secondary').addClass('button-primary');
      $(this).find('.shinjiru-select-label').text('Active Template');
      $(this).find('input[type="radio"]').prop('checked', true);
    });

    // Media Uploader for Brand Logo
    var mediaFrame;
    $('#shinjiru_upload_logo_btn').on('click', function(e) {
      e.preventDefault();
      if (mediaFrame) {
        mediaFrame.open();
        return;
      }

      mediaFrame = wp.media({
        title: shinjiruMaintData.mediaTitle,
        button: { text: shinjiruMaintData.mediaButton },
        multiple: false
      });

      mediaFrame.on('select', function() {
        var attachment = mediaFrame.state().get('selection').first().toJSON();
        $('#shinjiru_logo_url').val(attachment.url);
        $('#shinjiru_logo_id').val(attachment.id);
        $('#shinjiru_logo_img').attr('src', attachment.url).show();
        $('.shinjiru-logo-placeholder').hide();
        $('#shinjiru_remove_logo_btn').show();
      });

      mediaFrame.open();
    });

    // Remove logo
    $('#shinjiru_remove_logo_btn').on('click', function(e) {
      e.preventDefault();
      $('#shinjiru_logo_url').val('');
      $('#shinjiru_logo_id').val('0');
      $('#shinjiru_logo_img').attr('src', '').hide();
      $('.shinjiru-logo-placeholder').show();
      $(this).hide();
    });

    // Copy Secret Bypass URL
    $('#shinjiru_copy_bypass_btn').on('click', function(e) {
      e.preventDefault();
      var field = document.getElementById('shinjiru_bypass_url_field');
      field.select();
      field.setSelectionRange(0, 99999);
      try {
        navigator.clipboard.writeText(field.value);
        var originalText = $(this).text();
        $(this).text('Copied!');
        setTimeout(function() {
          $('#shinjiru_copy_bypass_btn').text(originalText);
        }, 2000);
      } catch (err) {
        document.execCommand('copy');
      }
    });

    // Move any external WordPress notices into the dedicated notices container
    var $notices = $('.shinjiru-wrap').find('.notice, .updated, .error, .update-nag, .is-dismissible').not('#shinjiru-notices-container .notice');
    if ($notices.length) {
      $('#shinjiru-notices-container').append($notices);
    }

    // Prompt on token regeneration
    $('#shinjiru_regen_token_chk').on('change', function() {
      if ($(this).is(':checked')) {
        if (!confirm(shinjiruMaintData.confirmRegen)) {
          $(this).prop('checked', false);
        }
      }
    });
  });

})(jQuery);
