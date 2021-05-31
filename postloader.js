(function(){

  function Postloader(elem, options) {

    this.$elem = jQuery(elem);
    this.options = jQuery.extend({}, PostloaderOptions, options);

    this.$elem.on('submit', '.postloader-form', this.onFormSubmit.bind(this));
    this.$elem.on('reset', '.postloader-form', this.onFormReset.bind(this));
    this.$elem.on('change', '.postloader-form :input.autoload', this.onInputChange.bind(this));
    this.$elem.on('click', '.postloader-more-button', this.onMoreButtonClick.bind(this));

    jQuery(document).trigger('postloader.init', [this]);

    this.load(1);
  }

  Postloader.prototype.onFormSubmit = function(event) {
    event.preventDefault();
    this.load(1);
  };

  Postloader.prototype.onFormReset = function(event) {
    this.load(1);
  };

  Postloader.prototype.onInputChange = function(event) {
    this.load(1);
  };

  Postloader.prototype.onMoreButtonClick = function(event) {
    event.preventDefault();
    var page = parseInt(this.$elem.find('.postloader-form input[name="page"]').val());
    this.load(page + 1);
  };

  Postloader.prototype.load = function(page) {

    this.$elem.addClass('is-loading');

    this.$elem.find('.postloader-form input[name="page"]').val(page);

    jQuery.post(this.options.ajaxurl, this.$elem.find('.postloader-form').serialize(), function(response){

      console.log(response);

      this.$elem.removeClass('is-loading');

      if (response.page < response.max_num_pages) {
        this.$elem.addClass('has-more');
      } else {
        this.$elem.removeClass('has-more');
      }

      if (page === 1) {
        this.$elem.find('.postloader-content').html('');
      }

      this.$elem.find('.postloader-content').append(response.content);

      jQuery(document).trigger('postloader.response', [response, this]);

    }.bind(this));

  };

  window.Postloader = Postloader;

})();

(function(){
  jQuery.fn.postloader = function(options) {
    return this.each(function(){
      if (! jQuery(this).data('postloader')) {
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

(function(){
  document.addEventListener('DOMContentLoaded', function(){

    // Checkbox change. Update label 'active' class.
    jQuery('.postloader').on('change', '.postloader-form label input[type="checkbox"]', function(){
      var $label = jQuery(this).closest('label');
      if (jQuery(this).is(':checked')) {
        $label.addClass('active');
      } else {
        $label.removeClass('active');
      }
    });

    // Radio change. Update label 'active' class.
    var $active = jQuery('.postloader-form label input[type="radio"]:checked');
    jQuery('.postloader').on('click', '.postloader-form label input[type="radio"]', function(){
      if ($active) {
        $active.prop('checked', false).closest('label').removeClass('active');
        $active = null;
      }
      var $label = jQuery(this).closest('label');
      if (jQuery(this).is(':checked')) {
        $label.addClass('active');
        $active = jQuery(this);
      } else {
        $label.removeClass('active');
      }
    });
  });
})();
