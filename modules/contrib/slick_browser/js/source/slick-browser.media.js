/**
 * @file
 * Provides Slick Browser media switch utilitiy functions.
 *
 * This can be used for both Slick browsers and widgets.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Slick Browser media utility functions.
   *
   * @param {int} i
   *   The index of the current element.
   * @param {HTMLElement} media
   *   The media player HTML element.
   */
  function sbMedia(i, media) {
    var $media = $(media);
    var $sb = $media.closest('.sb');
    var $zoom = $('.sb__zoom', $sb);
    var $body = $sb.closest('body');
    var $wParent = window.parent.document;
    var $iframe = $('iframe[id*="entity_browser_iframe"]', $wParent);
    var h = window.innerHeight;
    var id = 'sb-target';

    /**
     * Play the media.
     *
     * @param {jQuery.Event} event
     *   The event triggered by a `click` event.
     */
    function play(event) {
      Drupal.slickBrowser.jump(id);

      $sb.addClass('sb--zoomed');
      $body.addClass('is-zoom');
      $('.ui-dialog:visible', $wParent).addClass('ui-dialog--zoom');

      window.setTimeout(function () {
        if ($media.find('.media__iframe').length) {
          var $clone = $media.clone(true, true);
          var $video = $clone.find('.media__iframe');

          $clone.find('img').remove();
          $clone.appendTo($zoom).removeClass('media--ratio--fluid').addClass('media--zoom media--ratio--169').css('padding', '');

          $clone.find('.media__icon--play').attr('title', Drupal.t('Close preview'));
          $media.find('.media__iframe').attr('src', '');
          $iframe.css({maxHeight: $video.height()});
        }
      }, 1200);
    }

    /**
     * Close the media.
     *
     * @param {jQuery.Event} event
     *   The event triggered by a `click` event.
     */
    function stop(event) {
      $zoom.empty();
      $sb.removeClass('sb--zoomed');
      $body.removeClass('is-zoom');
      $('.ui-dialog:visible', $wParent).removeClass('ui-dialog--zoom');
      $iframe.css('max-height', h);
      Drupal.slickBrowser.jump(id);
      $('.is-playing').removeClass('is-playing').find('iframe').remove();
    }

    $media.on('click.sbMediaPlay', '.media__icon--play', play);
    $media.on('click.sbMediaClose', '.media__icon--close', stop);
  }

  /**
   * Attaches Slick Browser media behavior to HTML element.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.slickBrowserMedia = {
    attach: function (context) {
      $('.media--player', context).once('sbMedia').each(sbMedia);
    },
    detach: function (context, setting, trigger) {
      if (trigger === 'unload') {
        $('.media--player', context).removeOnce('sbMedia').off('.sbMediaPlay .sbMediaClose');
      }
    }
  };

})(jQuery, Drupal);
