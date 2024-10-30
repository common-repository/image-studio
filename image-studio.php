<?php
/*
 * Plugin Name: Image Studio
 * Plugin URI:  https://insivia.com/image-studio
 * Description: Configure and create designer images for social media and ads from your posts, pages, and CPTs.
 * Version:     1.3.1
 * Author:      insivia
 * Author URI:  https://insivia.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.html
 */

//error_reporting(E_ALL);
//ini_set('display_errors', 'On');

$locale = 'wig';

// core initiation
if( !class_Exists('studioMainStart') ){
	class studioMainStart{
		var $locale;
		function __construct( $locale, $includes, $path ){
			$this->locale = $locale;
			
			// include files
			foreach( $includes as $single_path ){
				include( $path.$single_path );				
			}
			// calling localization
			add_action('plugins_loaded', array( $this, 'myplugin_init' ) );
		}
		function myplugin_init() {
		 $plugin_dir = basename(dirname(__FILE__));
		 load_plugin_textdomain( $this->locale , false, $plugin_dir );
		}
	}
	
	
}

// initiate main class
new studioMainStart( $locale, array(
	'modules/scripts.php',
	'modules/meta_box.php',
	 
	'modules/hooks.php',
	'modules/settings.php',
	'modules/ajax.php',
), dirname(__FILE__).'/' );

 
// cron proicesing
if ( ! wp_next_scheduled( 'check_edited_images' ) ) {
  wp_schedule_event( time(), 'hourly', 'check_edited_images' );
}

add_action( 'check_edited_images', 'wis_process_edited_images' );

function wis_process_edited_images() {
	global $wpdb, $chat_table;
	 
} 

 
?>