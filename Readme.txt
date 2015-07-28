=== Sam's Modal Login ===
Contributors: samplugins
Donate link: http://samplugins.com/donation
Tags: modal forgotten, modal login, modal register, modal sign in, WordPress Login, wordpress register form, wp login form, wp sign in
Requires at least: 3.5
Tested up to: 4.2

Add Modal Login and Resgister Forms to your site to suite your style.

== Description ==

Creates an easy to use login form that will display a pop up window. user login, User registration and forgotten password forms are accessible from the front end of your website using a custom widget, [modal_login] shortcode or add_modal_login_link(); PHP function.

**Features:**

* Two Form Layouts
* Set Redirect URL After Login
* Unlimited Color Styles
* Compatible Browsers: IE9, IE10, IE11, Firefox, Safari, Opera, Chrome
* Login Widget
* Translation Ready
* Regular Product Updates
* Free Support

Visit [Sam's Plugins](http://samplugins.com/ "Crafted WordPress Plugin") for awesome WordPress Plugins, Tutorials and Reviews.

== Installation ==

This section describes how to install the plugin and get it working.


1. Go to Plugins > Add New
2. Under Upload, Click Browse, Locate sam-modal-login.zip in your plugin download package and click open.
3. FInd the WordPRess plugin you wish to install.
4. Click Install Now to intall the WordPress Plugin.
5. A popup window will ask you to confirm your wisht to install the plugin.
6. If this is first time you've installed a WordPress plugin, you may need to enter the FTP login credentails information. If you have installed a plugin before, it will still have the login infromation. This information is avilable through your web server host.
7. Click Proceed to continue with the installation. The resulting instaltion screen will list the installtion as successful or note anu problems durin the install.
8. If successful, click Activate Plugin to activate it.


== Screenshots ==

1. 
2. 

== How to use ==

**Adding Login Widget to a Sidebar:**

To setup a login widget navigate to Appearence => Widgets and use the drag and drop interface to drop "Modal Login" widget into the desired sidebar area.

Widget Options:

* Title: Add a title to the widget.
* Login Test: Set the Test for the login link, defaults to "Login"
* Logout Text: Set the text for the logout link, defaults to "Logout"
* SHow Admin link: Displays a link to the admin URL ehen a user is logged in.


**Adding Login and Register Links using SHortcode:**

You can use the [modal_login] shortcode in within edit screen posts, pages and custom post types. Inset the shortcode and click "Update".

Display Login Form:

[modal_login login_text="Login" logout_text="Logout"]

login_text : The text for the login link, defaults to "Login"
logout_text : The text for the logout link, default "Logout"

Dipaly Register Form:

[modal_login form="register" register_text="Register" logged_in_text="You are already logged in"]

register_text : The text for the register link, defaults to "Register"
logged_in_text : The text for already logged in user, defaults to "you are already registered and logged in".
form="register" The modal Will open directly to register form.


**Adding Login Link Using a PHP Code:**

Add the following PHP function within your tempalte files:

<?phpadd_modal_login_link(); ?>

Display Login Form:

<?php add_modal_login_link($login_text = 'Login, $logout_text = 'Logout', 4shoe-admin = true); ?>

$login_text : |String| The text for the login link, defaults to "Login".
$logout_text : |string| The text for the logout link, default "Logout".
$shoe-admin : |Bool| The setting to display the link to admin area when logged in, default is set to "false".


Display Register Form:

<?php add-modal_login_link($form = 'register' $register_text = 'Register', $logged_in_text = 'You are already logged in'); ?>

$register_text : |String| The text for the register link, defaults to "Register".
$logged_in_text : |String| The text for already logged in user, defaults to "You are already registerd and logged in".
$form : |String| Register| The modal will open directly to register form.


**Adding Login and Register links to WP Menu:**

Navigate to Appearence => Menus, under Edit Menus check under Sam Modal Link - Login/Logout and Register, click Add to Menu and Save Menu.

If you dont see Modal Login link in left sidebar, go to Screen Options and check Sam Modal Login under show on screen.



**Setip Options:**

By default after successfull login you will be redirected to the login page and on logout you will be redirected to homepage.

To set alternative login and logout redirect URL, navigate to settings => Modal Login setup section. Set redirect URLs and click "Save changes".

Allow new users to sset their own password by checking "Net user Password" checkbox.


*Styling Options:**

The plugin allows foe extensive styleing using color pickers to match any side. To set styling navigate o settings => Modal Login style section. Select Login form layout and set colors for element and click "Save Changes".

**Email Template:*

To set custom new user registration email template navigate to settings => Modal Login Registration email section. Enter template content and click "Save Changes".

Add user name, password, login link to template using: %username%, %password%, %loginlink%.