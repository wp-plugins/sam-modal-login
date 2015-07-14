<?php

/*-----------------------------------------------------------------------------------*/
/* Create the admin menu */
/*-----------------------------------------------------------------------------------*/

function sam_admin_resources() {
	wp_enqueue_script( 'sam-admin-script', SAM_PLUGIN_ASSETS_URL . 'js/login-admin.js', array( 'jquery' ), '2.0.5', true );
}


/*-----------------------------------------------------------------------------------*/
/* Register the admin page with the 'admin_menu' */
/*-----------------------------------------------------------------------------------*/

function sam_admin_menu() {
	$page = add_submenu_page( 'options-general.php', __( 'Sam Modal Login', 'sam' ), __( 'Sam Modal Login', 'sam' ), 'manage_options', 'sam-options', 'sam_options', 99 );

	add_action( 'admin_print_styles-' . $page, 'sam_admin_resources' );
}
add_action( 'admin_menu', 'sam_admin_menu' );


/*-----------------------------------------------------------------------------------*/
/* Load HTML that will create the outter shell of the admin page */
/*-----------------------------------------------------------------------------------*/

function sam_options() {
    // Check that the user is able to view this page.
	if ( ! current_user_can( 'manage_options' ) )
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'sam' ) ); ?>

	<div class="wrap">
		<div id="icon-themes" class="icon32"></div>
		<h2><?php _e( 'Modal Login Settings', 'sam' ); ?></h2>

		<?php // settings_errors(); ?>

		<form action="options.php" method="post">
			<?php settings_fields( 'sam_setup_options' ); ?>
			<?php do_settings_sections( 'sam_setup_options' ); ?>
			<?php settings_fields( 'sam_style_options' ); ?>
			<?php do_settings_sections( 'sam_style_options' ); ?>
            <?php settings_fields( 'sam_email_options' ); ?>
			<?php do_settings_sections( 'sam_email_options' ); ?>
			<?php submit_button(); ?>
		</form>

	</div>
<?php }

/*-----------------------------------------------------------------------------------*/
/* Registers all sections and fields with the Settings API */
/*-----------------------------------------------------------------------------------*/

function sam_init_settings_registration() {
	$option_name = 'sam_options';

	// Check if settings options exist in the database. If not, add them.
	if ( get_option( 'sam_options' ) )
		add_option( 'sam_options' );
	// Define settings sections.
	add_settings_section( 'sam_setup_section', __( 'Setup', 'sam' )  , 'sam_setup_options', 'sam_setup_options' );
	add_settings_section( 'sam_style_section', __( 'Style', 'sam' )  , 'sam_style_options', 'sam_style_options' );
	add_settings_section( 'sam_email_section', __( 'Registration Email', 'sam' ) , 'sam_email_options', 'sam_email_options' );
	add_settings_field( 'widget_info', __( 'Login Widget', 'sam' ), 'sam_settings_field_info', 'sam_setup_options', 'sam_setup_section', array(
		'options-name'	=> $option_name,
		'id'			=> 'widget-info',
		'value'			=> __( 'To add login widget to a sidebar navigate to Appearance > Widgets and drag "Modal Widget" to a sidebar.', 'sam' ),
	) );
	add_settings_field( 'shortcode_info', __( 'Shortcode', 'sam' ), 'sam_settings_field_info', 'sam_setup_options', 'sam_setup_section', array(
		'options-name'	=> $option_name,
		'id'			=> 'shortcode-info',
		'value'			=> __( 'Add the following shortcode to a page: [modal_login]', 'sam' ),
	) );
	add_settings_field( 'php_info', __( 'PHP Code', 'sam' ), 'sam_settings_field_info', 'sam_setup_options', 'sam_setup_section', array(
		'options-name'	=> $option_name,
		'id'			=> 'php-info',
		'value'			=> sprintf( __( 'Add the following function in your theme file for example header file: %s', 'sam' ), '<?php add_modal_login_link(); ?>' ),
	) );
	add_settings_field( 'login_redirect_url', __( 'Login Redirect URL', 'sam' ), 'sam_settings_field_text', 'sam_setup_options', 'sam_setup_section', array(
		'options-name'	=> $option_name,
		'id'			=> 'login-redirect-url',
		'label'			=> __( 'Set optional login redirect URL, if not set you will be redirected to login page.', 'sam' ),
	) );
	add_settings_field( 'logout_redirect_url', __( 'Logout Redirect URL', 'sam' ), 'sam_settings_field_text', 'sam_setup_options', 'sam_setup_section', array(
		'options-name'	=> $option_name,
		'id'			=> 'logout-redirect-url',
		'label'			=> __( 'Set optional logout redirect URL, if not set you will be redirected to home page.', 'sam' ),
	) );
	if (function_exists('pa_init_user_roles')) {
		add_settings_field( 'add_to_user_menu', __( 'Add To Theme User Menu', 'sam' ), 'sam_settings_field_checkbox', 'sam_setup_options', 'sam_setup_section', array(
			'options-name'	=> $option_name,
			'id'			=> 'add_to_user_menu',
				'value'			=> 'true',
			'label'			=> __( 'Add Login/Logout link to theme user menu', 'sam' ),
		) );
	}
    add_settings_field( 'userdefine_password', __( 'User Genrated Password', 'sam' ), 'sam_settings_field_checkbox', 'sam_setup_options', 'sam_setup_section', array(
		'options-name'	=> $option_name,
		'id'			=> 'userdefine_password',
		'value'			=> 'true',
		'label'			=> __( 'Allow users to enter their own password during registration.', 'sam' ),
	) );
	add_settings_field( 'modal_theme', __( 'Select Layout', 'sam' ), 'sam_settings_field_select', 'sam_style_options', 'sam_style_section', array(
		'options-name'	=> $option_name,
		'id'			=> 'modal-theme',
		'value'			=> array(
			'default' => __( 'Default' , 'sam' ),
			'wide' => __( 'Wide', 'sam' ),
		),
		'label'			=> __( 'Select modal login box layout.', 'sam' ),
	) );
	add_settings_field( 'modal_labels', __( 'Display Labels', 'sam' ), 'sam_settings_field_select', 'sam_style_options', 'sam_style_section', array(
		'options-name'	=> $option_name,
		'id'			=> 'modal-labels',
		'value'			=> array(
			'labels' => __( 'Labels' , 'sam' ),
			'placeholders' => __( 'Placeholders', 'sam' ),
		),
		'label'			=> __( 'Display textfield labels or placeholders.', 'sam' ),
	) );
	add_settings_field( 'bkg_color', __( 'Background Color', 'sam' ), 'sam_settings_field_color', 'sam_style_options', 'sam_style_section', array(
		'options-name'	=> $option_name,
		'id'			=> 'bkg-color',
		'label'			=> __( 'Set modal box background color.', 'sam' ),
	) );
	add_settings_field( 'font_color', __( 'Font Color', 'sam' ), 'sam_settings_field_color', 'sam_style_options', 'sam_style_section', array(
		'options-name'	=> $option_name,
		'id'			=> 'font-color',
		'label'			=> __( 'Set modal box font color.', 'sam' ),
	) );
	add_settings_field( 'link_color', __( 'Link Color', 'sam' ), 'sam_settings_field_color', 'sam_style_options', 'sam_style_section', array(
		'options-name'	=> $option_name,
		'id'			=> 'link-color',
		'label'			=> __( 'Set modal box link color.', 'sam' ),
	) );
	add_settings_field( 'btn_color', __( 'Button Color', 'sam' ), 'sam_settings_field_color', 'sam_style_options', 'sam_style_section', array(
		'options-name'	=> $option_name,
		'id'			=> 'btn-color',
		'label'			=> __( 'Set modal box button color.', 'sam' ),
	) );
	add_settings_field( 'custom_css', __( 'Custom CSS', 'sam' ), 'sam_settings_field_textarea', 'sam_style_options', 'sam_style_section', array(
		'options-name'	=> $option_name,
		'id'			=> 'custom-css',
		'label'			=> __( 'Add custom CSS code.', 'sam' ),
	) );
	add_settings_field( 'reg_email_subject', __( 'Email Subject', 'sam' ), 'sam_settings_field_text', 'sam_email_options', 'sam_email_section', array(
		'options-name'	=> $option_name,
		'id'			=> 'reg_email_subject',
		'label'			=> '',
	) );
	add_settings_field( 'reg_email_template', __( 'Email Body', 'sam' ), 'sam_settings_field_textarea', 'sam_email_options', 'sam_email_section', array(
		'options-name'	=> $option_name,
		'id'			=> 'reg_email_template',
		'label'			=> __( 'Add new user registration email template: %username%, %password%, %loginlink%. Leave blank to use default template.', 'sam' ),
	) );


	// Register settings with WordPress so we can save to the Database
	register_setting( 'sam_setup_options' , 'sam_options', 'sam_options_sanitize' );
	register_setting( 'sam_style_options' , 'sam_options', 'sam_options_sanitize' );
	register_setting( 'sam_email_options' , 'sam_options', 'sam_options_sanitize' );
}
add_action( 'admin_init', 'sam_init_settings_registration' );

/*-----------------------------------------------------------------------------------*/
/* add_settings_section() function for the widget options */
/*-----------------------------------------------------------------------------------*/

function sam_setup_options() {
	echo '<p>' . __( 'You can add login/logout link to your site in the following ways (for more setup information see plugin documentation included in the download package):', 'sam' ) . '.</p>';
}


/*-----------------------------------------------------------------------------------*/
/* add_settings_section() function for the widget options */
/*-----------------------------------------------------------------------------------*/

function sam_style_options() {
	echo '<p>' . __( 'Customize the look and feel of the modal login', 'sam' ) . '.</p>';
}

function sam_email_options(){
        echo '<p>' . __( 'Customize the content of new user registration email', 'sam' ) . '.</p>';
}
/*-----------------------------------------------------------------------------------*/
/* he callback function to display textareas */
/*-----------------------------------------------------------------------------------*/

function sam_settings_field_textarea( $args ) {
	// Set the options-name value to a variable
	$name = $args['options-name'] . '[' . $args['id'] . ']';

	// Get the options from the database
	$options = get_option( $args['options-name'] ); ?>

	<label for="<?php echo $args['id']; ?>"><?php esc_attr_e( $args['label'] ); ?></label><br />
	<textarea name="<?php echo $name; ?>" id="<?php echo $args['id']; ?>" class="<?php if ( ! empty( $args['class'] ) ) echo ' ' . $args['class']; ?>" cols="80" rows="8"><?php esc_attr_e( $options[ $args['id'] ] ); ?></textarea>
<?php }


/*-----------------------------------------------------------------------------------*/
/* The callback function to display checkboxes */
/*-----------------------------------------------------------------------------------*/

function sam_settings_field_checkbox( $args ) {
	// Set the options-name value to a variable
	$name = $args['options-name'] . '[' . $args['id'] . ']';

	// Get the options from the database
	$options = get_option( $args['options-name'] ); ?>

	<input type="checkbox" name="<?php echo $name; ?>" id="<?php echo $args['id']; ?>" <?php if ( ! empty( $args['class'] ) ) echo 'class="' . $args['class'] . '" '; ?>value="<?php esc_attr_e( $args['value'] ); ?>" <?php if ( isset( $options[ $args['id'] ] ) ) checked( $args['value'], $options[ $args['id'] ], true ); ?> />
	<label for="<?php echo $args['id']; ?>"><?php esc_attr_e( $args['label'] ); ?></label>
<?php }


/*-----------------------------------------------------------------------------------*/
/* The callback function to display selection dropdown */
/*-----------------------------------------------------------------------------------*/

function sam_settings_field_select( $args ) {
	// Set the options-name value to a variable
	$name = $args['options-name'] . '[' . $args['id'] . ']';

	// Get the options from the database
	$options = get_option( $args['options-name'] ); ?>

	<select name="<?php echo $name; ?>" id="<?php echo $args['id']; ?>" <?php if ( ! empty( $args['class'] ) ) echo 'class="' . $args['class'] . '" '; ?>>
		<?php foreach ( $args['value'] as $key => $value ) : ?>
			<option value="<?php esc_attr_e( $key ); ?>"<?php if ( isset( $options[ $args['id'] ] ) ) selected( $key, $options[ $args['id'] ], true ); ?>><?php esc_attr_e( $value ); ?></option>
		<?php endforeach; ?>
	</select>
	<label for="<?php echo $args['id']; ?>" style="display:block;"><?php esc_attr_e( $args['label'] ); ?></label>
<?php }

function sam_settings_field_checkboxes( $args ) {
	// Set the options-name value to a variable
        $options = get_option( $args['options-name'],array() );
        foreach ( $args['value'] as $key => $value ) :
            $name_key   = $args['id'] . '_' . $key; 
            $name       = $args['options-name'] . '[' . $name_key . ']';
            ?>
            <label for="<?php echo $args['id'] . '_'  . $key; ?>">
                <input type="checkbox" name="<?php echo $name ?>" id="<?php echo $args['id'] . '_'  . $key; ?>" 
                    <?php if ( ! empty( $args['class'] ) ) echo 'class="' . $args['class'] . '" '; ?> 
                    <?php
                        echo (((key_exists($name_key,$options))?(bool)$options[$name_key]:FALSE)?'checked="checked"':'');
                    ?>   
                    value="<?php esc_attr_e( $key ); ?>"
                />
                <?php esc_attr_e( $value ); ?>
            </label>
            <br/>
            <?php 
        endforeach; 
        
        ?>
	<label style="display:block;"><?php esc_attr_e( $args['label'] ); ?></label>
<?php }
/*-----------------------------------------------------------------------------------*/
/* Color picker */
/*-----------------------------------------------------------------------------------*/

function wp_enqueue_color_picker( ) {
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker-script', SAM_PLUGIN_ASSETS_URL . 'js/modal-login.js', array( 'wp-color-picker' ), false, true );
}
add_action( 'admin_enqueue_scripts', 'wp_enqueue_color_picker' );

/*-----------------------------------------------------------------------------------*/
/* The callback function to display color picker */
/*-----------------------------------------------------------------------------------*/

function sam_settings_field_color( $args ) {

	// Set the options-name value to a variable
	$name = $args['options-name'] . '[' . $args['id'] . ']';

	// Get the options from the database
	$options = get_option( $args['options-name'] ); ?>

	<input name="<?php echo $name; ?>" id="<?php echo $args['id']; ?>" class="wp-color-picker-field<?php if ( ! empty( $args['class'] ) ) echo ' ' . $args['class']; ?>" value="<?php if ( isset ( $options[ $args['id'] ] )) { esc_attr_e( $options[ $args['id'] ] ) ;} else { echo ''; } ?>"></input>

	<label for="<?php echo $args['id']; ?>" style="display:block;"><?php esc_attr_e( $args['label'] ); ?></label>
<?php }


/*-----------------------------------------------------------------------------------*/
/* The callback function to display text field */
/*-----------------------------------------------------------------------------------*/

function sam_settings_field_text( $args ) {

	// Set the options-name value to a variable
	$name = $args['options-name'] . '[' . $args['id'] . ']';

	// Get the options from the database
	$options = get_option( $args['options-name'] ); ?>

	<input name="<?php echo $name; ?>" id="<?php echo $args['id']; ?>" type="text" class="regular-text code<?php if ( ! empty( $args['class'] ) ) echo ' ' . $args['class']; ?>" value="<?php if ( isset ( $options[ $args['id'] ] )) { esc_attr_e( $options[ $args['id'] ] ) ;} else { echo ''; } ?>"></input>

	<label for="<?php echo $args['id']; ?>" style="display:block;"><?php esc_attr_e( $args['label'] ); ?></label>
<?php }


/*-----------------------------------------------------------------------------------*/
/* The callback function to display info */
/*-----------------------------------------------------------------------------------*/

function sam_settings_field_info( $args ) {
	// Set the options-name value to a variable
	$name = $args['options-name'] . '[' . $args['id'] . ']';

	// Get the options from the database
	$options = get_option( $args['options-name'] ); ?>

	<p><?php esc_attr_e( $args['value'] ); ?></p>

<?php }


/*-----------------------------------------------------------------------------------*/
/* Sanitization function */
/*-----------------------------------------------------------------------------------*/

function sam_options_sanitize( $input ) {

	// Set array for the sanitized options
	$output = array();

	// Loop through each of $input options and sanitize them.
	foreach ( $input as $key => $value ) {
		if ( isset( $input[ $key ] ) )
			$output[ $key ] = strip_tags( stripslashes( $input[ $key ] ) );
	}

	return apply_filters( 'sam_options_sanitize', $output, $input );
}

