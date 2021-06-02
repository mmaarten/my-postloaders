(function(window){
  "use strict";
  window = window || {};

  function Postloader(elem, options) {
    this.$elem = jQuery(elem);
    this.options = jQuery.extend({}, PostloaderOptions, options);

    this.$elem.on('submit', '.postloader-form', this.onFormSubmit.bind(this));
    this.$elem.on('reset', '.postloader-form', this.onFormReset.bind(this));
    this.$elem.on('change', '.postloader-form .autoload', this.onAutoloadInputChange.bind(this));
    this.$elem.on('click', '.postloader-more-button', this.onMoreButtonClick.bind(this));

    jQuery(document).trigger('postloader.init', [this]);

    this.load(1);
  }

  Postloader.prototype.onFormSubmit = function() {
    this.load(1);
  };

  Postloader.prototype.onFormReset = function() {
    setTimeout(function(){
      this.load(1);
    }.bind(this), 300);
  };

  Postloader.prototype.onAutoloadInputChange = function() {
    this.load(1);
  };

  Postloader.prototype.onMoreButtonClick = function() {
    var page = parseInt(this.$elem.find('.postloader-form input[name="page"]').val());
    this.load(page + 1);
  };

  Postloader.prototype.load = function(page) {

    this.$elem.find('.postloader-form input[name="page"]').val(page);
    this.$elem.addClass('is-loading');

    jQuery.post(this.options.ajaxurl, this.$elem.find('.postloader-form').serialize(), function(response){
      this.$elem.removeClass('is-loading');
      this.$elem.find('.postloader-form input[name="page"]').val(response.page);

      if (response.page < response.totalPages) {
        this.$elem.addClass('has-more');
      } else {
        this.$elem.removeClass('has-more');
      }

      if (page === 1) {
        this.$elem.find('.postloader-content').html('');
      }

      this.$elem.find('.postloader-content').append(response.content);

      jQuery(document).trigger('postloader.loadComplete', [this]);
    }.bind(this));
  };

  window.Postloader = Postloader;

})(window);
(function(undefined){
  jQuery.fn.postloader = function(options) {
    return this.each(function(){
      if (undefined === jQuery(this).data('postloader')) {
        jQuery(this).data('postloader', new Postloader(this, options));
      }
    });
  }
})();
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    jQuery('.postloader').postloader();
  });
})();
