<?php

/*-----------------------------------------------------------------------------------*/
/* Core modal login class */
/*-----------------------------------------------------------------------------------*/

class sam_class {

	// Set the version number
	public $plugin_version = '1.0.0';


/*-----------------------------------------------------------------------------------*/
/* Loads all of required hooks and filters and other cool doodads */
/*-----------------------------------------------------------------------------------*/

	public function __construct() {
		global $sam_options;

		// Register source code with the wp_footer().
		add_action( 'wp_footer', array( $this, 'sam_login_form' ) );

		// Add JavaScript and Stylesheets to the front-end.
		add_action( 'wp_enqueue_scripts', array( $this, 'sam_resources' ) );

		// Custom styles
		add_action( 'wp_head', array( $this, 'sam_print_style_css' ) );
		
		// Javascript
		add_action( 'wp_footer', array( $this, 'sam_print_style_js' ) );
		
		// Add the users custom CSS if they wish to add any.
		if ( $sam_options['custom-css'] ) {
			add_action( 'wp_head', array( $this, 'sam_print_custom_css' ) );
		}

		// Add lost password field.
		add_action( 'after_modal_form', array( $this, 'sam_additional_options' ) );

		// Add shortcode action.
		add_shortcode( 'modal_login', array( $this, 'sam_shortcode' ) );

		// Register widget.
		add_action( 'widgets_init', create_function( '', 'register_widget( "SAM_Widget" );' ) );

		// Run Ajax on the login.
		add_action( 'wp_ajax_nopriv_ajaxlogin'  , array( $this, 'sam_ajax_login' ) );
		add_action( 'wp_ajax_ajaxlogin'         , array( $this, 'sam_ajax_login' ) );

		// Add nav menu metabox
		add_filter( 'admin_init', array( $this, 'sam_add_nav_menu_metabox' ) );

		// Use the right label when displaying modal login/logout link
		add_filter( 'wp_nav_menu_objects', array($this, 'sam_filter_frontend_modal_link_label' ) );

		// Setup modal links attributes
		add_filter( 'nav_menu_link_attributes', array( $this, 'sam_filter_frontend_modal_link_atts' ) , 10, 3 ) ;

		// Hide registration link occasionally
		add_filter( 'wp_nav_menu_objects', array( $this, 'sam_filter_frontend_modal_link_register_hide' ) );
	}

/*-----------------------------------------------------------------------------------*/
/* Add menu link metabox */
/*-----------------------------------------------------------------------------------*/

	public function sam_add_nav_menu_metabox() {
		add_meta_box('sam_metabox_modal_link', __('PA Modal Link', 'sam' ), array( $this, 'sam_callback_metabox_modal_link' ), 'nav-menus', 'side', 'high');

		wp_register_script( 'sam-admin-nav-menus', SAM_PLUGIN_ASSETS_URL . 'js/menu-admin.js', array( 'jquery' ) );
		wp_enqueue_script( 'sam-admin-nav-menus' );


		$strings = array(
			'label_login' => __( 'Login Label', 'sam' ),
			'label_logout' => __( 'Logout Label', 'sam' ),
		);
		wp_localize_script( 'sam-admin-nav-menus', 'sam_strings', $strings );

	}


/*-----------------------------------------------------------------------------------*/
/* Add all scripts and styles to WordPress. */
/*-----------------------------------------------------------------------------------*/

	public function sam_resources() {
		global $sam_options;

		$login_url = $sam_options['login-redirect-url'];
		if ( empty ( $login_url ) ) {
			$login_url = $_SERVER['REQUEST_URI'];
		}

		$theme = $sam_options['modal-theme'];
		$labels = $sam_options['modal-labels'];

		wp_enqueue_style( 'sam-styles', SAM_PLUGIN_ASSETS_URL .'css/modal-login.css', null, $this->plugin_version, 'screen' );

		// Load the right Modal Theme
		if ( ! isset( $theme ) || $theme == 'default' ) {

		} elseif ( $theme == 'wide' ) {
			wp_enqueue_style( 'theme-wide', SAM_PLUGIN_ASSETS_URL . 'css/theme-wide.css', null, $this->plugin_version, 'screen' );
		}
		if ( $labels == 'placeholders' ) {
			wp_enqueue_style( 'labels', SAM_PLUGIN_ASSETS_URL . 'css/labels.css', null, $this->plugin_version, 'screen' );
		}
		
		wp_enqueue_script( 'sam-modal', SAM_PLUGIN_ASSETS_URL . 'js/modal.js', array( 'jquery' ), $this->plugin_version, true );
		wp_enqueue_script( 'sam-script', SAM_PLUGIN_ASSETS_URL . 'js/modal-login.js', array( 'jquery' ), $this->plugin_version, true );
		
		// Only run our ajax stuff when the user isn't logged in.
		if ( ! is_user_logged_in() ) {
			wp_localize_script( 'sam-script', 'modal_login_script', array(
				'ajax' 		     => admin_url( 'admin-ajax.php' ),
				'redirecturl' 	  => $login_url,
				'loadingmessage' => __( 'Checking Credentials...', 'sam' ),
			) );
		}
	}


	public function sam_print_style_css() {
		global $sam_options;

		echo '<style text="text/css" id="modal-login-style-css">';
		if ( $sam_options['bkg-color'] ) {
			echo '.ml-content { background-color: ' . sanitize_text_field( $sam_options['bkg-color'] ) . "}\n" ;
			if ( $sam_options['bkg-color'] != '#ffffff' ) {
				echo '#modal-login input[type="text"], #modal-login input[type="password"] { border: solid 1px ' . $sam_options['bkg-color'] . "; }\n" ;
				echo '#modal-login input[type="text"]:focus, #modal-login input[type="password"]:focus { border: solid 1px ' . $sam_options['bkg-color'] . "; }\n" ;
			}
		}
		if ( $sam_options['font-color'] ) {
			echo '#modal-login, #modal-login h2 { color: ' . sanitize_text_field( $sam_options['font-color'] ) . "}\n" ;
		}
		if ( $sam_options['link-color'] ) {
			echo '#additional-settings, #additional-settings a, #additional-settings a:hover { color: ' . sanitize_text_field( $sam_options['link-color'] ) . "}\n" ;
		}
		if ( $sam_options['btn-color'] ) {
			echo '#modal-login .submit .button { background: ' . sanitize_text_field( $sam_options['btn-color'] ) . "}\n" ;
			echo '#modal-login .submit .button { border: none; ' . "}\n" ;
		}
		echo "</style>\n";
	}

	public function sam_print_style_js() {

		// Many themes break the menu behaviour by forgetting an 'apply_filter' at 'nav_menu_link_attributes', that's why we re-add links attributes with jQuery
		if( !is_user_logged_in() ) { 
			?>
			<script type="text/javascript">
				jQuery( document ).ready( function($) {
					$( 'a[href="#pa_modal_login"]' )
						.attr( 'href', '#modal-login' )
						.attr( 'data-toggle', 'ml-modal' )
					;
					$( 'a[href="#pa_modal_register"]' )
						.attr( 'href', '#modal-register' )
						.attr( 'data-toggle', 'ml-modal' )
					;
				} );
			</script>
			<?php 
		} else { 
			?>
			<script type="text/javascript">
				jQuery( document ).ready( function($) {
					$( 'a[href="#pa_modal_login"]' ).attr( 'href', '<?php echo wp_logout_url() ?>'.replace( '&amp;', '&' ) );
				} );
			</script>
			<?php
		}

	}

/*-----------------------------------------------------------------------------------*/
/* Display any custom CSS */
/*-----------------------------------------------------------------------------------*/

	public function sam_print_custom_css() {
		global $sam_options;

		echo '<style text="text/css" id="modal-login-custom-css">' . sanitize_text_field( $sam_options['custom-css'] ) . "</style>\n";
	}


/*-----------------------------------------------------------------------------------*/
/* The main Ajax function  */
/*-----------------------------------------------------------------------------------*/

	public function sam_ajax_login() {
		global $sam_options;
		// Check our nonce and make sure it's correct.
                if(is_user_logged_in()){
                    echo json_encode( array(
                            'loggedin' => false,
                            'message'  => __( 'You are already logged in', 'sam' ),
                    ) );
                    die();
                }
		check_ajax_referer( 'ajax-form-nonce', 'security' );

		// Get our form data.
		$data = array();

		// Check that we are submitting the login form
		if ( isset( $_REQUEST['login'] ) )  {
                        
			$data['user_login']         = sanitize_user( $_REQUEST['username'] );
			$data['user_password']      = sanitize_text_field( $_REQUEST['password'] );
			$data['remember']           = (sanitize_text_field( $_REQUEST['rememberme'] )=='TRUE')?TRUE:FALSE;
			$user_login                 = wp_signon( $data, false );

			// Check the results of our login and provide the needed feedback
			if ( is_wp_error( $user_login ) ) {
				echo json_encode( array(
					'loggedin' => false,
					'message'  => __( 'Wrong Username or Password!', 'sam' ),
				) );
			} else {
				echo json_encode( array(
					'loggedin' => true,
					'message'  => __( 'Login Successful!', 'sam' ),
				) );
			}
		}

		// Check if we are submitting the register form
		elseif ( isset( $_REQUEST['register'] ) ) {
			$user_data = array(
				'user_login' => sanitize_user( $_REQUEST['username'] ),
				'user_email' => sanitize_email( $_REQUEST['email'] ),
			);
			$user_register = $this->sam_register_new_user( $user_data['user_login'], $user_data['user_email'] );

			// Check if there were any issues with creating the new user
			if ( is_wp_error( $user_register ) ) {
				echo json_encode( array(
					'registerd' => false,
					'message'   => $user_register->get_error_message(),
				) );
			} else {
                if(isset($sam_options['userdefine_password'])){
                    $success_message = __( 'Registration complete.', 'sam' );
                }else{
                    $success_message = __( 'Registration complete. Check your email.', 'sam' );
                }
				echo json_encode( array(
					'registerd'     => true,
                                        'redirect'      => (isset($sam_options['userdefine_password'])?TRUE:FALSE),
					'message'	=> $success_message,
				) );
			}
		}

		// Check if we are submitting the forgotten pwd form
		elseif ( isset( $_REQUEST['forgotten'] ) ) {

			// Check if we are sending an email or username and sanitize it appropriately
			if ( is_email( $_REQUEST['username'] ) ) {
				$username = sanitize_email( $_REQUEST['username'] );
			} else {
				$username = sanitize_user( $_REQUEST['username'] );
			}

			// Send our information
			$user_forgotten = $this->sam_retrieve_password( $username );

			// Check if there were any errors when requesting a new password
			if ( is_wp_error( $user_forgotten ) ) {
				echo json_encode( array(
					'reset' 	 => false,
					'message' => $user_forgotten->get_error_message(),
				) );
			} else {
				echo json_encode( array(
					'reset'   => true,
					'message' => __( 'Password Reset. Please check your email.', 'sam' ),
				) );
			}
		}

		die();
	}


/*-----------------------------------------------------------------------------------*/
/* Sanitize user entered information */
/*-----------------------------------------------------------------------------------*/

	public function sam_register_new_user( $user_login, $user_email ) {
		global $sam_options;
		$labels = $sam_options['modal-labels'];
                
		$errors = new WP_Error();
		$sanitized_user_login = sanitize_user( $user_login );
		$user_email = apply_filters( 'user_registration_email', $user_email );

		// Check the username was sanitized
		if ( $sanitized_user_login == '' ) {
			$errors->add( 'empty_username', __( 'Please enter a username.', 'sam' ) );
		} elseif ( ! validate_username( $user_login ) ) {
			$errors->add( 'invalid_username', __( 'This username is invalid because it uses illegal characters. Please enter a valid username.', 'sam' ) );
			$sanitized_user_login = '';
		} elseif ( username_exists( $sanitized_user_login ) ) {
			$errors->add( 'username_exists', __( 'This username is already registered. Please choose another one.', 'sam' ) );
		}

		// Check the email address
		if ( $user_email == '' ) {
			$errors->add( 'empty_email', __( 'Please type your email address.', 'sam' ) );
		} elseif ( ! is_email( $user_email ) ) {
			$errors->add( 'invalid_email', __( 'The email address isn\'t correct.', 'sam' ) );
			$user_email = '';
		} elseif ( email_exists( $user_email ) ) {
			$errors->add( 'email_exists', __( 'This email is already registered, please choose another one.', 'sam' ) );
		}
        /**
         * password Validation if the User Defined Password Is Allowed
         */
        if(isset($sam_options['userdefine_password'])){
            if(empty($_REQUEST['password'])){
                $errors->add( 'empty_password', __( 'Please type your password.', 'sam' ) );
            }elseif (strlen($_REQUEST['password'])<6) {
                $errors->add( 'minlength_password', __( 'Password must be 6 character long.', 'sam' ) );
            }elseif ($_REQUEST['password'] != $_REQUEST['cpassword']) {
                $errors->add( 'unequal_password', __( 'Passwords do not match.', 'sam' ) );
            }
        }
                
		$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

		if ( $errors->get_error_code() )
			return $errors;
                $user_pass = (isset($sam_options['userdefine_password']))?$_REQUEST['password']:wp_generate_password( 12, false );
		$user_id = wp_create_user( $sanitized_user_login, $user_pass, $user_email );

		if ( ! $user_id ) {
			$errors->add( 'registerfail', __( 'Couldn\'t register you... please contact the site administrator', 'sam' ) );

			return $errors;
		}

		update_user_option( $user_id, 'default_password_nag', true, true ); // Set up the Password change nag.
                
                if(isset($sam_options['userdefine_password'])){
                    $data['user_login']             = $user_login;
                    $data['user_password']          = $user_pass;
                    $user_login                     = wp_signon( $data, false );
                }
                
                $user = get_userdata( $user_id );
                // The blogname option is escaped with esc_html on the way into the database in sanitize_option
                // we want to reverse this for the plain text arena of emails.
                $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

                $message  = sprintf(__('New user registration on your site %s:', 'sam'), $blogname) . "\r\n\r\n";
                $message .= sprintf(__('Username: %s', 'sam'), $user->user_login) . "\r\n\r\n";
                $message .= sprintf(__('Email: %s', 'sam'), $user->user_email) . "\r\n";

                @wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration', 'sam'), $blogname), $message);

                if ( empty($user_pass) )
                        return;

                $message  = sprintf(__('Username: %s', 'sam'), $user->user_login) . "\r\n";
                $message .= sprintf(__('Password: %s', 'sam'), $user_pass) . "\r\n";
                $message .= wp_login_url() . "\r\n";

                
                
                $email_detail   = array(
                    'subject'   => sprintf(__('[%s] Your username and password', 'sam'), $blogname),
                    'body'      => $message,
                );
                
                
                $pattern        = array('#\%username\%#','#\%password\%#','#\%loginlink\%#');
                $replacement    = array($user->user_login,$user_pass,wp_login_url());
                $subject        = trim($sam_options['reg_email_subject']);
                $body           = trim($sam_options['reg_email_template']);
                
                if(!empty($subject))
                    $email_detail['subject'] = @preg_replace($pattern,$replacement, $subject);
                
                if(!empty($body))
                    $email_detail['body']    = @preg_replace($pattern,$replacement, $body);
                
                
                @wp_mail($user->user_email,$email_detail['subject'] , $email_detail['body']);
                
                //@todo
		//wp_new_user_notification( $user_id, $user_pass );

		return $user_id;
	}

/*-----------------------------------------------------------------------------------*/
/* Setup password retrieve function */
/*-----------------------------------------------------------------------------------*/

	function sam_retrieve_password( $user_data ) {
		global $wpdb, $current_site;

		$errors = new WP_Error();

		if ( empty( $user_data ) ) {
			$errors->add( 'empty_username', __( 'Please enter a username or e-mail address.', 'sam' ) );
		} else if ( strpos( $user_data, '@' ) ) {
			$user_data = get_user_by( 'email', trim( $user_data ) );
			if ( empty( $user_data ) )
				$errors->add( 'invalid_email', __( 'There is no user registered with that email address.', 'sam'  ) );
		} else {
			$login = trim( $user_data );
			$user_data = get_user_by( 'login', $login );
		}

		do_action( 'lostpassword_post' );

		if ( $errors->get_error_code() )
			return $errors;

		if ( ! $user_data ) {
			$errors->add( 'invalidcombo', __( 'Invalid username or e-mail.', 'sam' ) );
			return $errors;
		}

		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		do_action( 'retreive_password', $user_login );  // Misspelled and deprecated
		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

		if ( ! $allow )
			return new WP_Error( 'no_password_reset', __( 'Password reset is not allowed for this user', 'sam' ) );
		else if ( is_wp_error( $allow ) )
			return $allow;

        $key = wp_generate_password( 20, false );
        
        do_action( 'retrieve_password_key', $user_login, $key );
        
        require_once ABSPATH . 'wp-includes/class-phpass.php';
        $wp_hasher = new PasswordHash( 8, true );
        
        $hashed = $wp_hasher->HashPassword( $key );
        
        // Now insert the new md5 key into the db
        $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );
     
     	$message = __( 'Someone requested that the password be reset for the following account:', 'sam' ) . "\r\n\r\n";
		$message .= network_home_url( '/' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', 'sam' ), $user_login ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.', 'sam' ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:', 'sam' ) . "\r\n\r\n";
		$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n";

		if ( is_multisite() ) {
			$blogname = $GLOBALS['current_site']->site_name;
		} else {
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$title   = sprintf( __( '[%s] Password Reset', 'sam' ), $blogname );
		$title   = apply_filters( 'retrieve_password_title', $title );
		$message = apply_filters( 'retrieve_password_message', $message, $key );

		if ( $message && ! wp_mail( $user_email, $title, $message ) ) {
			$errors->add( 'noemail', __( 'The e-mail could not be sent. Possible reason: your host may have disabled the mail() function.', 'sam' ) );

			return $errors;

			wp_die();
		}

		return true;
	}

/*-----------------------------------------------------------------------------------*/
/* Login form */
/*-----------------------------------------------------------------------------------*/

	public function sam_login_form() {
		global $user_ID, $user_identity, $sam_options;
		get_currentuserinfo();
		$multisite_reg = get_site_option( 'registration' ); 

		global $sam_options;
		$labels = $sam_options['modal-labels'];
		?>

		<div id="modal-login" class="ml-modal fade" tabindex="-1" role="dialog" aria-hidden="true">

			<?php do_action( 'before_modal_title' ); ?>

			<?php if( ! $user_ID ) : ?>

			<div class="modal-login-dialog">
		      <div class="ml-content">

				<div class="section-container">

					<?php // Login Form ?>
					<div id="login" class="modal-login-content">

						<button type="button" class="ml-close" data-dismiss="ml-modal" aria-hidden="true">&times;</button>
						<h2><?php _e( 'Login', 'sam' ); ?></h2>

						<?php do_action( 'before_modal_login' ); ?>

						<form action="login" method="post" id="form" class="group" name="loginform">

							<?php do_action( 'inside_modal_login_first' ); ?>

							<p class="mluser">
								<label class="field-titles" for="login_user"><?php _e( 'Username', 'sam' ); ?></label>
								<input type="text" name="log" id="login_user" class="input" placeholder="<?php if ( $labels == 'placeholders' ) { _e( 'Username', 'sam' ); } ?>" value="<?php if ( isset( $user_login ) ) echo esc_attr( $user_login ); ?>" size="20" />
							</p>

							<p class="mlpsw">
								<label class="field-titles" for="login_pass"><?php _e( 'Password', 'sam' ); ?></label>
								<input type="password" name="pwd" id="login_pass" class="input" placeholder="<?php if ( $labels == 'placeholders' ) { _e( 'Password', 'sam' ); } ?>" value="" size="20" />
							</p>

							<?php do_action( 'sam_login_form' ); ?>

							<p id="forgetmenot">
								<label class="forgetmenot-label" for="rememberme"><input name="rememberme" type="checkbox" placeholder="<?php if ( $labels == 'placeholders' ) { _e( 'Password', 'sam' ); } ?>" id="rememberme" value="forever" /> <?php _e( 'Remember Me', 'sam' ); ?></label>
							</p>

							<p class="submit">

								<?php do_action( 'inside_modal_login_submit' ); ?>

								<input type="submit" name="wp-sumbit" id="wp-submit" class="button button-primary button-large" value="<?php _e( 'Log In', 'sam' ); ?>" />
								<input type="hidden" name="login" value="true" />
								<?php wp_nonce_field( 'ajax-form-nonce', 'security' ); ?>

							</p><!--[END .submit]-->

							<?php do_action( 'inside_modal_login_last' ); ?>

						</form><!--[END #loginform]-->
					</div><!--[END #login]-->

					<?php // Registration form ?>
					<?php if ( ( get_option( 'users_can_register' ) && ! is_multisite() ) || ( $multisite_reg == 'all' || $multisite_reg == 'blog' || $multisite_reg == 'user' ) ) : ?>
						<div id="register" class="modal-login-content" style="display:none;">

							<button type="button" class="ml-close" data-dismiss="ml-modal" aria-hidden="true">&times;</button>
							<h2><?php _e( 'Register', 'sam' ); ?></h2>

							<?php do_action( 'before_modal_register' ); ?>

							<form action="register" method="post" id="form" class="group" name="loginform">

								<?php do_action( 'inside_modal_register_first' ); ?>

								<p class="mluser">
									<label class="field-titles" for="reg_user"><?php _e( 'Username', 'sam' ); ?></label>
									<input type="text" name="user_login" id="reg_user" class="input" placeholder="<?php if ( $labels == 'placeholders' ) { _e( 'Username', 'sam' ); } ?>" value="<?php if ( isset( $user_login ) ) echo esc_attr( stripslashes( $user_login ) ); ?>" size="20" />
								</p>

								<p class="mlemail">
									<label class="field-titles" for="reg_email"><?php _e( 'Email', 'sam' ); ?></label>
									<input type="text" name="user_email" id="reg_email" class="input" placeholder="<?php if ( $labels == 'placeholders' ) { _e( 'Email', 'sam' ); } ?>" value="<?php if ( isset( $user_email ) ) echo esc_attr( stripslashes( $user_email ) ); ?>" size="20" />
								</p>
                                    <?php
                                    if(isset($sam_options['userdefine_password'])){
                                    ?>
                                <p class="mlregpsw">
									<label class="field-titles" for="reg_password"><?php _e( 'Password', 'sam' ); ?></label>
									<input type="password" name="reg_password" id="reg_password" class="input" placeholder="<?php if ( $labels == 'placeholders' ) { _e( 'Password', 'sam' ); } ?>"  />
								</p>
                                <p class="mlregpswconf">
									<label class="field-titles" for="reg_cpassword"><?php _e( 'Confirm Password', 'sam' ); ?></label>
									<input type="password" name="reg_cpassword" id="reg_cpassword" class="input" placeholder="<?php if ( $labels == 'placeholders' ) { _e( 'Confirm Password', 'sam' ); } ?>"  />
								</p>
                                    <?php 
                                    }
                                    ?>
                                                                
								<?php do_action( 'register_form' ); ?>

								<p class="submit">

									<?php do_action( 'inside_modal_register_submit' ); ?>

									<input type="submit" name="user-sumbit" id="user-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Sign Up', 'sam' ); ?>" />
									<input type="hidden" name="register" value="true" />
									<?php wp_nonce_field( 'ajax-form-nonce', 'security' ); ?>

								</p><!--[END .submit]-->

								<?php do_action( 'inside_modal_register_last' ); ?>

							</form>

						</div><!--[END #register]-->
					<?php endif; ?>

					<?php // Forgotten Password ?>
					<div id="forgotten" class="modal-login-content" style="display:none;">

						<button type="button" class="ml-close" data-dismiss="ml-modal" aria-hidden="true">&times;</button>
						<h2><?php _e( 'Forgotten Password?', 'sam' ); ?></h2>

						<?php do_action( 'before_modal_forgotten' ); ?>

						<form action="forgotten" method="post" id="form" class="group" name="loginform">

							<?php do_action( 'inside_modal_forgotton_first' ); ?>

							<p class="mlforgt">
								<label class="field-titles" for="forgot_login"><?php _e( 'Username or Email', 'sam' ); ?></label>
								<input type="text" name="forgot_login" id="forgot_login" class="input" placeholder="<?php if ( $labels == 'placeholders' ) { _e( 'Username or Email', 'sam' ); } ?>" value="<?php if ( isset( $user_login ) ) echo esc_attr( stripslashes( $user_login ) ); ?>" size="20" />
							</p>

							<?php do_action( 'sam_login_form', 'resetpass' ); ?>

							<p class="submit">

								<?php do_action( 'inside_modal_forgotten_submit' ); ?>

								<input type="submit" name="user-submit" id="user-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Reset Password', 'sam' ); ?>">
								<input type="hidden" name="forgotten" value="true" />
								<?php wp_nonce_field( 'ajax-form-nonce', 'security' ); ?>

							</p>

							<?php do_action( 'inside_modal_forgotten_last' ); ?>

						</form>

					</div><!--[END #forgotten]-->
				</div><!--[END .section-container]-->
			<?php endif; ?>

			<?php do_action( 'after_modal_form' ); ?>

				</div>
			</div>
		</div><!--[END #modal-login]-->
	<?php }


/*-----------------------------------------------------------------------------------*/
/* Add additional fields to the login_form(). Hooked through 'after_modal_form' */
/*-----------------------------------------------------------------------------------*/

	public function sam_additional_options() {
		global $sam_options;

		$multisite_reg = get_site_option( 'registration' );

		echo '<div id="additional-settings">';

		if ( ( get_option( 'users_can_register' ) && ! is_multisite() ) || ( $multisite_reg == 'all' || $multisite_reg == 'blog' || $multisite_reg == 'user' ) )
			echo '<a href="#register" class="modal-login-nav">' . __( 'Register', 'sam' ) . '</a> | ';

		echo '<a href="#forgotten" class="modal-login-nav">' . __( 'Lost your password?', 'sam' ) . '</a>';

		echo '<div class="hide-login"> | <a href="#login" class="modal-login-nav">' . __( 'Back to Login', 'sam' ) . '</a></div>';

		echo '</div>';
	}


/*-----------------------------------------------------------------------------------*/
/* "Back to login form" button */
/*-----------------------------------------------------------------------------------*/

	public function sam_back_to_login() {
		echo '<a href="#login" class="modal-login-nav">' . __( 'Login', 'sam' ) . '</a>';
	}


/*-----------------------------------------------------------------------------------*/
/* HTML for login link */
/*-----------------------------------------------------------------------------------*/

	public function modal_login_btn( $login_text = 'Login', $logout_text = 'Logout', $show_admin = 1 ) {
		// Check if we have an over riding logout redirection set. Other wise, default to the home page.
		global $sam_options;
		$logout_url = $sam_options['logout-redirect-url'];
		if ( isset( $logout_url ) && $logout_url == '' )
			$logout_url = home_url();

		// Is the user logged in? If so, serve them the logout button, else we'll show the login button.
		if ( is_user_logged_in() ) {
			$link = '<a href="' . wp_logout_url( esc_url( $logout_url ) ) . '" class="login">' . sprintf( _x( '%s', 'Logout Text', 'sam' ), sanitize_text_field( $logout_text ) ) . '</a>';
			if ( $show_admin )
				$link .= ' | <a href="' . esc_url( admin_url() ) . '">' . __( 'View Admin', 'sam' ) . '</a>';
		} else {
			$link = '<a href="#modal-login" class="login" data-toggle="ml-modal">' . sprintf( _x( '%s', 'Login Text', 'sam' ), sanitize_text_field( $login_text ) ) . '</a>';
		}

		return $link;
	}


/*-----------------------------------------------------------------------------------*/
/* The shortcode function [wp-modal-login] */
/*-----------------------------------------------------------------------------------*/

	public function sam_shortcode( $atts ) {
		extract( shortcode_atts( array(
			'login_text'  => __( 'Login', 'sam'),
			'logout_text' => __( 'Logout', 'sam'),
			'logged_in_text' => __( 'You are already registered and logged in', 'sam'),
			'register_text' => __( 'Register', 'sam'),
			'form' => 'login',
		), $atts ) );

		global $sam_options;
		$logout_url = $sam_options['logout-redirect-url'];
		if ( isset( $logout_url ) && $logout_url == '' )
			$logout_url = home_url();

		if( 'register' === $form ) {
			if( ! is_user_logged_in() ) {
				$link = '<a href="#modal-register" class="register" data-toggle="ml-modal">' . $register_text . '</a>';
			} else {
				$link = $logged_in_text;
			}
		} else if( 'login' === $form ) {
		 	if ( is_user_logged_in() ) {
				$link = '<a href="' . wp_logout_url( esc_url( $logout_url ) ) . '" class="login" data-toggle="ml-modal">' . sprintf( _x( '%s', 'Shortcode Logout Text', 'sam' ), sanitize_text_field( $logout_text ) ) . '</a>';
			} else {
				$link = '<a href="#modal-login" class="login" data-toggle="ml-modal">' . sprintf( _x( '%s', 'Shortcode Login Text', 'sam' ), sanitize_text_field( $login_text ) ) . '</a>';
			}
		}

		return $link;
	}


/*-----------------------------------------------------------------------------------*/
/* Menu link metabox generator function */
/*-----------------------------------------------------------------------------------*/

	public function sam_callback_metabox_modal_link() {

		?>
		<div id="posttype-sam-modal-link" class="posttypediv">
			<div id="tabs-panel-sam-modal-link" class="tabs-panel tabs-panel-active">
				<ul id ="sam-modal-link-checklist" class="categorychecklist form-no-clear">
					<li>
						<label class="menu-item-title">
						<input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="-1"> <?php _e('Login', 'sam' ); ?> / <?php _e('Logout', 'sam' ); ?>
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom">
						<input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="<?php _e('Login', 'sam' ); ?> // <?php _e('Logout', 'sam' ); ?>">
						<input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="#pa_modal_login">
					</li>
						<li>
						<label class="menu-item-title">
						<input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="-1"> <?php _e('Register', 'sam' ); ?>
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom">
						<input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="<?php _e('Register', 'sam' ); ?>">
						<input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="#pa_modal_register">
					</li>
				</ul>
			</div>
			<p class="button-controls">
				<span class="add-to-menu">
					<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php _e( 'Add to Menu' ); ?>" name="add-post-type-menu-item" id="submit-posttype-sam-modal-link">
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

/*-----------------------------------------------------------------------------------*/
/* Use the right labelfor the modal link */
/*-----------------------------------------------------------------------------------*/
	public function sam_filter_frontend_modal_link_label( $items ) {
		foreach ( $items as $i => $item ) {
			if( '#pa_modal_login' === $item->url ) {
				$item_parts = explode( ' // ', $item->title );
				if ( is_user_logged_in() ) {
					$items[ $i ]->title = array_pop( $item_parts );
				} else {
					$items[ $i ]->title = array_shift( $item_parts );
				}
			}
		}
		return $items;    
	}

/*-----------------------------------------------------------------------------------*/
/* Use the right label for the modal link */
/*-----------------------------------------------------------------------------------*/
	public function sam_filter_frontend_modal_link_atts( $atts, $item, $args ) {

		// Only apply when URL is #pa_modal_login/#pa_modal_register
		if( '#pa_modal_login' === $atts[ 'href'] ) {
			// Check if we have an over riding logout redirection set. Other wise, default to the home page.
			global $sam_options;
			$logout_url = $sam_options['logout-redirect-url'];
			if ( isset( $logout_url ) && $logout_url == '' )
				$logout_url = home_url();

			// Is the user logged in? If so, serve them the logout button, else we'll show the login button.
			if ( is_user_logged_in() ) {
				$atts[ 'href' ] = wp_logout_url( esc_url( $logout_url ) );
			} else {
				$atts[ 'href' ] = '#modal-login';
				$atts[ 'data-toggle' ] = 'ml-modal';
			}
		} else if ( '#pa_modal_register' === $atts[ 'href'] ) {
			$atts[ 'href' ] = '#modal-register';
			$atts[ 'data-toggle' ] = 'ml-modal';
		}

		return $atts;
	}

/*-----------------------------------------------------------------------------------*/
/* Hide registration link from menus for logged in users */
/*-----------------------------------------------------------------------------------*/
	public function sam_filter_frontend_modal_link_register_hide( $items ) {
		foreach ( $items as $i => $item ) {
			if( '#pa_modal_register' === $item->url ) {
				if ( is_user_logged_in() ) {
					unset( $items[ $i ] );
				}
			}
		}
		return $items;    
	}

}
