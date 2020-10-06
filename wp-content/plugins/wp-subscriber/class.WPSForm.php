<?php
class WPSForm {

  public static function init() {
    add_shortcode( 'wps_form', [__CLASS__, 'echo_form'] );
  }

  public static function echo_form() {
    wp_register_script( 'wps-client', WPS_PLUGIN_URL . '/wps-client.js', ['jquery'] );
    wp_enqueue_script( 'wps-client' );

    ?>
    <form id="wps__form">
      <div class="wps__form-row">
        <label for="full_name">Your name</label>
        <input id="full_name" type="text" name="full_name" required/>
      </div>
      <div class="wps__form-row">
        <label for="email">Your email</label>
        <input id="email" type="email" name="email" required/>
      </div>
      <div class="wps__form-agreement">
        <input id="gave_concent" type="checkbox" name="gave_concent" value="1" required>
        <label for="gave_concent">I agree</label>
      </div>
      <button type="submit" class="wps__form-submit">
        Submit
      </button>
    </form>
    <?php
  }

  public static function process_form( WP_REST_Request $request ) {
    global $wpdb;
    $email = $request->get_param('email');
    $rows = $wpdb->get_row( "SELECT * FROM " . WPS_DB_TABLE . " WHERE `email`='" . $email . "'" );

    if ($rows) {
      return new WP_Error( 'already_subscribed', 'You\'ve already subscribed', ['status' => 500] );
    }

    $result = $wpdb->insert( WPS_DB_TABLE, $request->get_params(), ['%s', '%s', '%d']);
    if (!$result) {
      return new WP_Error( 'unknown_error', 'Something went wrong, refresh the page and try again', ['status' => 500] );
    }

    return $result;
  }
}
