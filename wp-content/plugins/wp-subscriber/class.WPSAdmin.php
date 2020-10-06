<?php
class WPSAdmin {

  public static function init() {
    add_action( 'admin_menu', [__CLASS__, 'admin_menu'] );
  }

  public static function admin_menu() {
    add_menu_page( 'Subscribers', 'Subscribers', 'administrator', 'wpsubscribers', [__CLASS__, 'admin_page'] );
  }

  public static function admin_page() {

    $list = self::get_users();
    ?>
    <div class="">
      <form action="/wp-json/wps/import" method="post" enctype="multipart/form-data">
        <input type="file" name="file">
        <button type="submit" id="wps__import">Import CSV</button>
        <a style="margin-left:2rem" role="button" target="_blank" href="/wp-json/wps/export" id="wps__export">Export CSV</a>
      </form>

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Full name</th>
            <th>Email</th>
            <th>Gave concent</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list as $row) : ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= $row['full_name'] ?></td>
              <td><?= $row['email'] ?></td>
              <td><?= $row['gave_concent'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    </div>
    <?
  }

  public static function import_csv( WP_REST_Request $request ) {
    global $wpdb;

    $files = $request->get_file_params();
    if (empty($files)) {
      return new WP_Error( 'no_files', 'No files uploaded', ['status' => 500] );
    }

    $file = $files['file'];
    $fp = fopen( $file['tmp_name'], 'r' );
    while ( ( $data = fgetcsv( $fp, 1000, ',' ) ) !== false ) {
      $rows = $wpdb->get_row( "SELECT * FROM " . WPS_DB_TABLE . " WHERE `email`='" . $data[2] . "'" );
      if ($rows) {
        continue;
      }

      $result = $wpdb->insert( WPS_DB_TABLE, [
        'full_name' => $data[1],
        'email' => $data[2],
        'gave_concent' => $data[3],
      ], ['%s', '%s', '%d']);
    }

    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
  }

  public static function export_csv() {
    $list = self::get_users();
    $file = WPS_PLUGIN_DIR . 'subscribers.csv';
    $fp = fopen($file, 'w');

    foreach ($list as $fields) {
      fputcsv($fp, $fields);
    }

    fclose($fp);

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
  }

  private static function get_users() {
    global $wpdb;
    $result = $wpdb->get_results( "SELECT * FROM " . WPS_DB_TABLE, ARRAY_A);

    return $result;
  }
}
