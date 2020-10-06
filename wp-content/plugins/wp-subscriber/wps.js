jQuery(function() {
  jQuery('#wps__form').on('submit', function(e) {
    e.preventDefault();
    $form = jQuery(this);

    jQuery.ajax({
      url: '/wp-json/wps/subscribe',
      method: 'POST',
      data: $form.serialize(),
      success: function(response) {
        alert('Success');
      },
      error: function(err) {
        alert('Error: ' + err.message);
      }
    });
  })
});
