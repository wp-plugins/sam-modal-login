<?php
/*
Plugin Name: Sam Modal Login
Plugin URI: http://samplugins.com/sam-wordpress-modal-login
Description: Modal login form with redirect and styling options created by Sam.
Version: 1.0.0
Author: Sam Plugins
Author URI: http://www.samplugins.com
Text Domain: sam
License: GPLv2 or later
*/

/*-----------------------------------------------------------------------------------*/
/* Return option page data */
/*-----------------------------------------------------------------------------------*/
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])){
    if($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
        $_SERVER['HTTPS'] = 'on';
} 
    

$sam_options = get_option( 'sam_options' );

/*-----------------------------------------------------------------------------------*/
/* Define Constants */
/*-----------------------------------------------------------------------------------*/

define( 'SAM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SAM_PLUGIN_URL', plugins_url("", __FILE__) );

define( 'SAM_PLUGIN_INCLUDES_DIR', SAM_PLUGIN_DIR . "/includes/" );
define( 'SAM_PLUGIN_INCLUDES_URL', SAM_PLUGIN_URL . "/includes/" );

define( 'SAM_PLUGIN_ASSETS_DIR', SAM_PLUGIN_DIR . "/assets/" );
define( 'SAM_PLUGIN_ASSETS_URL', SAM_PLUGIN_URL . "/assets/" );

/*-----------------------------------------------------------------------------------*/
/* Load text domain */
/*-----------------------------------------------------------------------------------*/

function sam_load_textdomain() {
	load_plugin_textdomain( 'sam', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'sam_load_textdomain' );

/*-----------------------------------------------------------------------------------*/
/* Load primary class */
/*-----------------------------------------------------------------------------------*/

require_once SAM_PLUGIN_INCLUDES_DIR. 'modal-login-class.php';

/*-----------------------------------------------------------------------------------*/
/* Load widget class */
/*-----------------------------------------------------------------------------------*/

require_once SAM_PLUGIN_INCLUDES_DIR . 'widget/modal-login-widget.php';

/*-----------------------------------------------------------------------------------*/
/* Load the admin page */
/*-----------------------------------------------------------------------------------*/

if ( is_admin() ) {
	require_once SAM_PLUGIN_INCLUDES_DIR . 'admin.php';
}

/*-----------------------------------------------------------------------------------*/
/* Login / logout links */
/*-----------------------------------------------------------------------------------*/

function add_modal_login_link( $login_text = 'Login', $logout_text = 'Logout', $show_admin = false ) {
	global $sam_class;

	if ( isset( $sam_class ) ) {
		echo $sam_class->modal_login_btn( $login_text, $logout_text, $show_admin );
	} else {
		echo __( 'Error: Modal Login class failed to load', 'sam' );
	}
}

/*-----------------------------------------------------------------------------------*/
/* Shortcode function  */
/*-----------------------------------------------------------------------------------*/
function modal_login( $params = array() ) {
	$params_str = '';
	foreach( $params as $parameter => $value ) {
		if( $value ) {
			$params_str .= sprintf( ' %s="%s"', $parameter, $value);
		}
	}
	echo do_shortcode( "[modal_login $params_str]" );
}

/*-----------------------------------------------------------------------------------*/
/* Load modal login class */
/*-----------------------------------------------------------------------------------*/

if ( class_exists( 'sam_class' ) ) {
	$sam_class = new sam_class;
}
