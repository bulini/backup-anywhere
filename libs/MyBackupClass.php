<?php
// Make sure the script can handle large folders/files
ini_set('max_execution_time', 600);
ini_set('memory_limit','2048M');

require_once(ABSPATH.'wp-content/plugins/backup-anywhere/vendor/autoload.php');

use Ifsnop\Mysqldump as IMysqldump;
use FtpClient\FtpClient;

if ( !class_exists( 'MyBackupClass' ) ) {

  class MyBackupClass
  {

    function __construct() {
      add_action('admin_menu', array($this,'my_backup_menu_page' ));
      add_action( 'admin_init', array($this,'register_backup_settings' ));
      add_action( 'admin_init', array( $this, 'page_init' ) );
      add_action('admin_enqueue_scripts', array($this,'my_backup_assets'));
      add_action('wp_ajax_zip_folders', array($this,'zip_folders'));
      add_action('wp_ajax_backup_database', array($this,'backup_database'));
      add_action('wp_ajax_upload_backup', array($this,'upload_backup'));
      add_filter( 'admin_body_class', array($this,'set_body_class'));
      add_action( 'admin_notices', array( $this, 'display_messages' ) );
      //add_action( 'admin_notices', array( $this, 'display_errors' ) );

    }

    /**
    * imposto la body class sulle pagine
    * my_backup
    * @param [type] $classes [description]
    */
    function set_body_class( $classes ) {
      $screen = get_current_screen();
      //print_r($screen);
      if ( $screen->id != 'toplevel_page_my_backup' &&  $screen->id != 'page_my_backup-settings' ) return;
      $classes .= ' my_backup ';
      return $classes;
    }

    /**
     * 
     */
    function zip_folders() {

      $folder=WPCONTENT_FOLDER;      
      $today = date("Y-m-d");
      // creo cartella con data odierna
      $backup_dir = BACKUP_FOLDER.$today;
      if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0700);
      }

      $zipTo= $backup_dir.'/wp-content.zip';

      if (extension_loaded('zip') === true) {
          if (file_exists($folder) === true) {
              $zip = new ZipArchive();
  
              if ($zip->open($zipTo, ZIPARCHIVE::CREATE) === true) {
                  $source = realpath($folder);
                  echo $source;
                  if (is_dir($source) === true) {
                      $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
  
                      foreach ($files as $file) {
                          $file = realpath($file);
                          echo $file;
                          if (is_dir($file) === true) {
                              $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                          } else if (is_file($file) === true) {
                              $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                          }
                      }
                  } else if (is_file($source) === true) {
                      $zip->addFromString(basename($source), file_get_contents($source));
                  }
              }
              $zip->close();
              return 'pippo';
          }
      }
      return false;
  }

    /**
    * my_backup assets (css / js)
    * @return [type] [description]
    */
    public function my_backup_assets() {
      wp_enqueue_script( 'sweetalert-js', plugins_url('backup-anywhere') . '/assets/js/sweetalert2.all.min.js');
      wp_enqueue_style( 'admin-api', plugins_url('backup-anywhere') . '/assets/css/admin-lite.css');
      wp_enqueue_style( 'sweetalert-css', plugins_url('backup-anywhere') . '/assets/css/sweetalert.css');
      wp_enqueue_script('app-js', plugins_url('backup-anywhere') . '/assets/js/app.js', array(
        'jquery'
      ), '1.0.4');
      wp_localize_script('app-js', 'app_data', array(
        'ajaxurl' => admin_url('admin-ajax.php') ,
        //'templateUrl' => get_stylesheet_directory_uri(),
        'nonce' => wp_create_nonce('api-nonce')
      ));
    }

    /**
    * Backup db
    * @return [type] [description]
    */
    public function backup_database() {
      $db_host = DB_HOST;
      $db_name = DB_NAME;
      $db_user = DB_USER;
      $db_password = DB_PASSWORD;
      $today = date("Y-m-d");
      // creo cartella con data odierna se non esiste
      $backup_dir = BACKUP_FOLDER . $today;
      if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0700);
      }

     try {
        $dump = new IMysqldump\Mysqldump("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
        $dump->start("$backup_dir/dump.sql");
      } catch (\Exception $e) {
          echo 'mysqldump-php error: ' . $e->getMessage();
      }
    }

    public function classic_upload() {
      $today = date("Y-m-d");
      $options = get_option('backup_options'); 
      $host = $options['ftp_host'];
      $login = $options['ftp_user'];
      $password = $options['ftp_password'];
      $remote_path = $options['ftp_path'];
      $backup_dir = BACKUP_FOLDER . $today;
      $remote_backup =  $remote_path.'/'.$today;
      // connect and login to FTP server
      $ftp_server = $host;
      $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
      $login = ftp_login($ftp_conn, $login, $password);

      $file = $backup_dir.'/wp-content.zip';

      // upload file
      if (ftp_put($ftp_conn, $remote_backup.'/wp-content.zip', $file, FTP_BINARY))
        {
        echo "Successfully uploaded $file.";
        }
      else
        {
        echo "Error uploading $file.";
        }

      // close connection
      ftp_close($ftp_conn);

    }

    /**
     * Tento di connettermi al server WM
     * se ok -> vai
     * altrimenti scatta l'exception del porca madonna
     */
    public function upload_backup() {
      $today = date("Y-m-d");
      $backup_dir = BACKUP_FOLDER . $today;
      $remote_backup = '/bk_test_giuseppe/'.$today;

      $options = get_option('backup_options'); 
      $host = $options['ftp_host'];
      $login = $options['ftp_user'];
      $password = $options['ftp_password'];

      try {
        $ftp = new \FtpClient\FtpClient();
        $ftp->connect($host);
        $ftp->login($login, $password);
        // $files = $ftp->scanDir('/bk_test_giuseppe');
        // print_r($files);
        // Creates a directory
        // Turns passive mode on or off
        $ftp->pasv(true);
        $ftp->mkdir("$remote_backup");
        $ftp->putAll($backup_dir, $remote_backup);
        
      } catch (\Throwable $th) {
        //throw $th;
        echo $th;
      }

    }



    /**
    * Admin Menu Screen my_backup
    * @return [type] [description]
    */
    function my_backup_menu_page(){
      add_menu_page(
        'Backup WP',
        'Backup WP',
        'manage_options',
        'my_backup',
        array($this,'my_backup_page'),
        'dashicons-backup',
        50
      );
      add_submenu_page( 'my_backup', 'Impostazioni Backup', 'Backup Settings', 'manage_options', 'my_backup-settings', array($this,'my_backup_settings_page'));
      add_submenu_page( 'my_backup', 'Impostazioni Backup', 'API Settings', 'manage_options', 'my_backup-api', array($this,'my_backup_api_page'));

    }

    /**
    * Pagina Admin Backup
    * @return [type] [description]
    */
    function my_backup_page(){ ?>
<div class="loader loader-off">Loading&#8230;</div>
<div class="wrap">
	<div class="row">
		<div class="col">
			<div class="title">
				<h1 class="wp-heading-inline">Backup Manager</h1>
				<h3>Do what you want</h3>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<div id="api-response">
			</div>
		</div>
		<div class="col">
			<!-- <p><?php $options = get_option('backup_options'); echo $options['ftp_host']; ?></p> -->

			<a class="button button-primary" id="backup_wp"><span class="dashicons dashicons-wordpress"></span> Backup
				wp-content</a>
			</p>
			<p>
				<a class="button button-primary" id="backup_db">
					<span class="dashicons dashicons-database-import"></span> Backup database
				</a>
			</p>
			<p>
				<a class="button button-primary" id="upload_bk"><span class="dashicons dashicons-cloud-upload"></span>
					Upload Backup</a>
			</p>
		</div>
	</div>
</div>
<?php
    }

    /**
    * Settings x API my_backup
    * @return [type] [description]
    */
    function register_backup_settings() {
      //register our settings
      register_setting( 'my_backup-group', 'my_backup_email_to' );
    }

    /**
    * Register and add settings
    */
    public function page_init()
    {
      register_setting(
        'my_backup_group', // Option group
        'backup_options', // Option name
        array( $this, 'sanitize' ) // Sanitize
      );

      add_settings_section(
        'setting_section_id', // ID
        'Backup Settings', // Title
        array( $this, 'print_section_info' ), // Callback
        'my_backup-admin' // Page
      );

      add_settings_field(
        'email_to', // ID
        'email', // Title
        array( $this, 'email_field' ), // Callback
        'my_backup-admin', // Page
        'setting_section_id' // Section
      );

      add_settings_field(
        'ftp_host', // ID
        'Host FTP', // Title
        array( $this, 'text_field' ), // Callback
        'my_backup-admin', // Page
        'setting_section_id', // Section
        'ftp_host'
      );

      add_settings_field(
        'ftp_user', // ID
        'Utente FTP', // Title
        array( $this, 'text_field' ), // Callback
        'my_backup-admin', // Page
        'setting_section_id', // Section
        'ftp_user'
      );

      add_settings_field(
        'ftp_password', // ID
        'Password FTP', // Title
        array( $this, 'password_field'), // Callback
        'my_backup-admin', // Page
        'setting_section_id', // Section
        'ftp_password'
      );

      add_settings_field(
        'ftp_path', // ID
        'Remote FTP path', // Title
        array( $this, 'text_field' ), // Callback
        'my_backup-admin', // Page
        'setting_section_id', // Section
        'ftp_path'
      );         
    }




    /**
    * Pagina Admin Settigs API
    * @return [type] [description]
    */
    function my_backup_settings_page(){
      $this->options = get_option( 'backup_options' ); ?>

<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>

	<div class="row">
		<div class="col-two-third float-l">
			<form method="post" action="<?php echo admin_url('options.php'); ?>">
				<?php
        settings_fields( 'my_backup_group' );
        do_settings_sections( 'my_backup-admin' );
        submit_button();
        ?>
			</form>
		</div>
		<div class="col-third float-r white-panel">


		</div>
	</div>
</div>

<?php
    }

    public function display_messages() {

      $screen = get_current_screen();

      if ($screen->id === 'page_my_backup-settings') {

        if (isset($_GET['settings-updated'])) {

          if ($_GET['settings-updated'] === 'true') : ?>

<div class="notice notice-success is-dismissible">
	<p><?php _e('Impostazioni salvate con successo.', 'bbb'); ?></p>
</div>

<?php endif;

      }
    }
  }

  /**
  * Print the Section text
  */
  public function print_section_info()
  {
    print 'Inserire impostazioni Backup';
  }

  /**
  * Get the settings option array and print one of its values
  */
  public function email_field()
  {
    printf(
      '<input type="email" required class="regular-text" id="email_to" name="backup_options[email_to]" value="%s" />',
      isset( $this->options['email_to'] ) ? esc_attr( $this->options['email_to']) : ''
    );
  }

  /**
  * Get the settings option array and print one of its values
  */
  public function text_field($field)
  {
    printf(
      '<input type="text" required class="regular-text" id="'.$field.'" name="backup_options['.$field.']" value="%s" />',
      isset( $this->options[$field] ) ? esc_attr( $this->options[$field]) : ''
    );
  }

  /**
  * Get the settings option array and print one of its values
  */
  public function password_field($field)
  {
    printf(
      '<input type="password" required class="regular-text" id="'.$field.'" name="backup_options['.$field.']" value="%s" />',
      isset( $this->options[$field] ) ? esc_attr( $this->options[$field]) : ''
    );
  }  

}

new MyBackupClass;
}
