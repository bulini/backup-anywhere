<?php
/**
 * Plugin Name:     Backup Anywhere
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
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

require_once('libs/MyBackupClass.php');
