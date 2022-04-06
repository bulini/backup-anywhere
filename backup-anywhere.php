<?php
/**
 * Plugin Name:     Backup Anywhere
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     Backup wpcontent e mysql customizzato dal GURU
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     backup-anywhere
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Backup_Anywhere
 */

// Your code starts here.
define('WPCONTENT_FOLDER', ABSPATH.'wp-content/');
define('BACKUP_FOLDER', ABSPATH.'wp-backup/' );

register_activation_hook( __FILE__, 'activate_backup_anywhere');


function activate_backup_anywhere() {
  $backup_dir = BACKUP_FOLDER;
  if (! is_dir($backup_dir)) {
    mkdir($backup_dir, 0700 );
  }
}

require_once('libs/MyBackupClass.php');
