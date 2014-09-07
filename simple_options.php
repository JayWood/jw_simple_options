<?php
/**********************************************************************************
					JW Simple Options
			Author:			Jerry Wood Jr.
			Email:			jay@plugish.com
			WWW:			http://plugish.com
  This sofware is provided free of charge.  I use this in my own projects and
  it's ideal for small options pages when you don't feel like writing the
  HTML to accompany it.  This work is Licensed under the Creative Commons
  Attribution Share-Alike 3.0 License.  All I ask, is you don't sell the
  software as is.  You're more than welcome to include it in a package
  as long as you give the appropriate credit.
**********************************************************************************/

class JW_SIMPLE_OPTIONS {

	/**
	 *	Simple options Version Number
	 *
	 * @access private
	 * @var string Class version number.
	 */
	private $ver = '1.3.1';

	/**
	 * Options array passed to class
	 *
	 * @access private
	 * @var array() A map of the options data.
	 */
	private $options;

	/**
	 * Location of the plugin/theme menu.
	 *
	 * Accepts: page, link, comment, management, option, theme, plugin, user, dashboard, post, or media.
	 *
	 * Type  		Location
	 * -----------------------------
	 * default		New tab
	 * page			Pages
	 * link			Link Manager
	 * comment		Comments
	 * management	Tools
	 * option 		Settings
	 * theme 		Appearance
	 * plugin		Plugin
	 * user			Users
	 * dashboard	Dashboard
	 * post			Posts
	 * media		Media
	 * 
	 * @access private
	 * @var string Default: 'new'
	 */
	private $menu_type = 'new';

	/**
	 * Used in options saving/gathering and menu data.
	 *
	 * @access private
	 * @var string Default: 'jw_'
	 */
	private $prefix = 'jw_';

	/**
	 * Reader friendly menu name.
	 *
	 * @access private
	 * @var string Default: 'JW Options'
	 */
	private $menu_title = 'JW Options';

	/**
	 * Capability needed by users to see this menu.
	 *
	 * @access private
	 * @var string Default: manage_options
	 * @see add_menu_page()
	 */
	private $cap = 'manage_options';

	/**
	 * URL friendly name of the menu, ie. 'options_page'
	 *
	 * Will be prefixed by prefix variable.
	 *
	 * @access private
	 * @var string Default: 'options_page'
	 */
	private $slug = 'options_page';

	/**
	 * Icon of the top-level menu.  Absolute URL.
	 *
	 * @access private
	 * @var string Default: NULL
	 */
	private $icon = NULL;

	/**
	 * Menu position of the top-level menu.  Used only if menu_type is 'new'.
	 *
	 * @access private
	 * @var integer Default: NULL
	 */
	private $pos = NULL;

	/**
	 * Used in menu pages and throughout the plugin
	 *
	 * @access private
	 * @var string Defaults to "JW Options Panel"
	 */
	private $plugin_title = 'JW Options Panel';

	/**
	 * Used in menu generation and hooks.
	 *
	 * @access private
	 * @var string
	 */
	private $hook;

	/**
	 * Set to true if using in a theme.
	 *
	 * @var boolean
	 */
	private $is_theme = false;

/*
	function JW_SIMPLE_OPTIONS( $ops ) {
		$this->__construct( $ops );
	}
*/
	function __construct( array $ops ) {
		// Setup variables
		$this->plugin_title = empty( $ops['plugin_title'] ) ? $this->plugin_title : $ops['plugin_title'];
		$this->menu_title = empty( $ops['menu_title'] ) ? $this->menu_title : $ops['menu_title'];
		$this->menu_type = empty( $ops['menu_type'] ) ? $this->menu_type : $ops['menu_type'];
		$this->cap = empty( $ops['capability'] ) ? $this->cap : $ops['capability'];
		$this->slug = empty( $ops['slug'] ) ? $this->prefix.$this->slug : $ops['slug'];
		$this->options = empty( $ops['opData'] ) ? $this->options : $ops['opData'];
		$this->icon = empty( $ops['icon_url'] ) ? $this->icon : $ops['icon_url'];
		$this->pos = empty( $ops['menu_pos'] ) ? $this->pos : $ops['menu_pos'];
		$this->prefix = empty( $ops['prefix'] ) ? $this->prefix : $ops['prefix'];
		$this->is_theme = empty( $ops['is_theme'] ) ? $this->is_theme : $ops['is_theme'];

		add_action( 'admin_init', array( $this, 'register_admin_deps' ) );
		add_action( 'admin_menu', array( $this, 'load_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_deps' ) );
	}

	/**
	 * Builds an array of check boxes.
	 *
	 * @param string  $key  Option identifier minus prefix.
	 * @param array   $data Associative array of data to display.
	 */
	public function buildCheckFields( $key, $data, $def = false ) {
		$opData = get_option( $this->prefix.$key, $def );
		ob_start();
?>
        	<fieldset>
			<?php foreach ( $data as $k => $v ): ?>
                <label for="<?php echo $this->prefix.$key; ?>_<?php echo $k; ?>" class="jw_check_fields">
                	<input id="<?php echo $this->prefix.$key; ?>_<?php echo $k; ?>" type="checkbox" name="<?php echo $this->prefix.$key; ?>[]" <?php $this->jop_checked( $opData, $k, true ); ?> value="<?php echo $k; ?>"/> <?php echo $v; ?>
                </label>
            <?php endforeach; ?>
            </fieldset>
        <?php
        
        $output = ob_get_clean();

		return $output;

	}

	/**
	 * Builds an array of data, comparable to a matrix.
	 *
	 * Also provides neat javascript functionality such as adding/removing rows.
	 *
	 * @param string  $key    Option identifier minus prefix.
	 * @param array   $fields A regular array of data identifiers, ie. array('field1', 'field2').
	 */
	public function buildDataArrayFields( $key, $fields, $showhead = false ) {
		$opData = get_option( $this->prefix.$key );
?>
        	<a href="javascript:;" class="addrow" data_id="<?php echo $key; ?>">[+] Add Row</a>
        	<table class="dataArrayFields" id="<?php echo $key; ?>">
			<?php $rowBase = 1; ?>
            <?php if ( $showhead ): ?>
                <thead>
                    <tr>
                    <?php foreach ( $fields as $k => $v ): ?>
                        <td><?php echo $v; ?></td>
                    <?php endforeach; ?>
                    </tr>
                </thead>
            <?php endif; ?>
            <?php if ( !empty( $opData ) && is_array( $opData ) ) :?>
            	<?php foreach ( $opData as $row ): ?>
                	<tr id="data_row_<?php echo $rowBase; ?>" class="data_row <?php echo $key; ?>">
					<?php foreach ( $fields as $colName => $colLabel ): ?>
                        <td class="data_col <?php echo $colName; ?>"><input type="text" name="<?php echo $this->prefix.$key ?>[<?php echo $rowBase; ?>][<?php echo $colName; ?>]" value="<?php echo $row[$colName]; ?>"/></td>
                    <?php endforeach; ?>
                        <td><a href="javascript:;" id="<?php echo $rowBase; ?>" class="removerow" curBlock="<?php echo $key; ?>">[X]</a></td>
                    </tr>
                    <?php $rowBase++; ?>
                <?php endforeach; ?>
            <?php else: ?>
            	<tr id="data_row_<?php echo $rowBase; ?>" class="data_row <?php echo $key; ?>">
            	<?php foreach ( $fields as $colName => $colLabel ): ?>
	                <td class="data_col <?php echo $colName; ?>"><input type="text" name="<?php echo $this->prefix.$key ?>[<?php echo $rowBase; ?>][<?php echo $colName; ?>]" /></td>
                <?php endforeach; ?>
                	<td><a href="javascript:;" id="<?php echo $rowBase; ?>" class="removerow <?php echo $key; ?>" curBlock="<?php echo $key; ?>">[X]</a></td>
                </tr>
            <?php endif; ?>
            </table>
        <?php
	}

	/**
	 * WordPress 3.5 media upload functionality.
	 *
	 * @param string  Option identifier minus prefix.
	 */
	public function buildMediaOption( $key ) {

		$opData = get_option( $this->prefix.$key );

		$output = '<div class="uploader">';
		$output .= '<input type="text" name="'.$this->prefix.$key.'" id="'.$this->prefix.$key.'" class="regular-text" value="'.$opData.'" />';
		$output .= '<input type="button" id="'.$this->prefix.$key.'_upload" value="Upload" class="button upload_image_button" data-id="'.$this->prefix.$key.'" />';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Builds an array of radio buttons.
	 *
	 * @param string  $key  Option identifier minus prefix.
	 * @param array   $data Associative array of data to display.
	 * @param boolean $def  If not false, provide a default value if no option exists.
	 */
	public function buildRadioFields( $key, $data, $def = false ) {
		$opData = get_option( $this->prefix.$key, $def );
?>
        	<fieldset>
			<?php foreach ( $data as $k => $v ): ?>
                <label for="<?php echo $this->prefix.$key; ?>_<?php echo $k; ?>" class="jw_radio_fields">
                	<input id="<?php echo $this->prefix.$key; ?>_<?php echo $k; ?>" type="radio" name="<?php echo $this->prefix.$key; ?>" <?php checked( $opData, $k, true ); ?> value="<?php echo $k; ?>"/> <?php echo $v; ?>
                </label>
            <?php endforeach; ?>
            </fieldset>
        <?php

	}

	/**
	 * Builds dropdown menu.
	 *
	 * @param string  $key  Option identifier minus prefix.
	 * @param array   $data Associative array of data to display.
	 * @param boolean $def  If not false, provide a default value if no option exists.
	 */
	public function buildSelectOptions( $key, $data, $def = false ) {

		$opData = get_option( $this->prefix.$key, $def );

		$output = '<select name="'.$this->prefix.$key.'" id="'.$this->prefix.$key.'">';
		foreach ( $data as $k => $v ) {
			$output .= '<option value="'.$k.'" '.selected( $opData, $k, false ).'>'.$v.'</option>';
		}
		$output .= '</select>';

		return $output;

	}
	/**
	 * Builds a timeframe selection that consists of one text input, and one dropdown.
	 *
	 * @param string  $key Option identifier minus prefix.
	 * @param mixed   $def If not false, provide a default value if no option exists.
	 */
	public function buildTimeframe( $key, $def = false ) {
		// Should be two fields, one input text, one dropdown.
		$opData = get_option( $this->prefix.$key, $def );

		if ( empty( $opData['multiplier'] ) ) $opData['mulitplier'] = $def['multiplier'];
		if ( empty( $opData['time'] ) ) $opData['time'] = $def['time'];

?>
        <input type="text" name="<?php echo $this->prefix.$key; ?>[multiplier]" value="<?php echo $opData['multiplier']; ?>" class="jw_multiplier"/><select name="<?php echo $this->prefix.$key; ?>[time]">
        	<option value="60" <?php selected( $opData['time'], 60, true ); ?>>Minutes</option>
            <option value="<?php echo 60*60; ?>" <?php selected( $opData['time'], 60*60, true ); ?>>Hours</option>
            <option value="<?php echo 60*60*24; ?>" <?php selected( $opData['time'], 60*60*24, true ); ?>>Days</option>
            <option value="<?php echo 60*60*24*30; ?>" <?php selected( $opData['time'], 60*60*24*30, true ); ?>>Months</option>
            <option value="<?php echo 60*60*24*365; ?>" <?php selected( $opData['time'], 60*60*24*365, true ); ?>>Years</option>
        </select>
        <?php
	}

	/**
	 * Custom Checked
	 *
	 * Allows using arrays in checked variables
	 *
	 * @return boolean
	 */
	function jop_checked( $haystack, $cur, $show = FALSE ) {
		if ( is_array( $haystack ) && in_array( $cur, $haystack ) ) {
			$cur = $haystack = 1;
		}
		return checked( $haystack, $cur, $show );
	}

	/**
	 * Loads the admin menu with user-defined flags.
	 */
	function load_admin_menu() {

		switch ( $this->menu_type ) {
		case 'page':
			$hook = add_pages_page( $this->plugin_title, $this->menu_title, $this->cap, $this->slug, array( &$this, 'render_options_page' ) );
			break;
		case 'link':
			$hook = add_links_page( $this->plugin_title, $this->menu_title, $this->cap, $this->slug, array( &$this, 'render_options_page' ) );
			break;
		case 'comment':
			$hook = add_comments_page( $this->plugin_title, $this->menu_title, $this->cap, $this->slug, array( &$this, 'render_options_page' ) );
			break;
		case 'management':
			$hook = add_management_page( $this->plugin_title, $this->menu_title, $this->cap, $this->slug, array( &$this, 'render_options_page' ) );
			break;
		case 'option':
			$hook = add_options_page( $this->plugin_title, $this->menu_title, $this->cap, $this->slug, array( &$this, 'render_options_page' ) );
			break;
		case 'theme':
			$hook = add_theme_page( $this->plugin_title, $this->menu_title, $this->cap, $this->slug, array( &$this, 'render_options_page' ) );
			break;
		case 'plugin':
			$hook = add_plugins_page( $this->plugin_title, $this->menu_title, $this->cap, $this->slug, array( &$this, 'render_options_page' ) );
			break;
		case 'user':
			$hook = add_users_page( $this->plugin_title, $this->menu_title, $this->cap, $this->slug, array( &$this, 'render_options_page' ) );
			break;
		case 'dashboard':
			$hook = add_dashboard_page( $this->plugin_title, $this->menu_title, $this->cap, $this->slug, array( &$this, 'render_options_page' ) );
			break;
		case 'post':
			$hook = add_posts_page( $this->plugin_title, $this->menu_title, $this->cap, $this->slug, array( &$this, 'render_options_page' ) );
			break;
		case 'media':
			$hook = add_media_page( $this->plugin_title, $this->menu_title, $this->cap, $this->slug, array( &$this, 'render_options_page' ) );
			break;
		default:
			$hook = add_menu_page( $this->plugin_title, $this->menu_title, $this->cap, $this->slug, array( &$this, 'render_options_page' ), $this->icon, isset($this->menu_pos) ? $this->menu_pos : null );
			break;
		}
		$this->hook = $hook;
	}

	/**
	 * Load up admin dependancies for functionality
	 */
	public function load_admin_deps( $hook = false ) {
		if ( $hook == $this->hook && $hook != false ) {

			if ( function_exists( 'wp_enqueue_media' ) ) wp_enqueue_media();

			wp_enqueue_style( 'spectrum' );
			wp_enqueue_script( 'spectrum' );

			wp_enqueue_style( $this->prefix.'admin_css' );
			wp_enqueue_script( $this->prefix.'admin_js' );
		}
	}

	/**
	 * Registering Admin information.
	 */
	public function register_admin_deps() {
		foreach ( $this->options as $k => $v ){
			register_setting( $this->prefix.'options', $this->prefix.$k );
			if( ! empty( $v['def'] ) ){
				// Then set the default value.
				add_option( $this->prefix.$k, $v['def'] );
			}

		}

		$sdir = ( $this->is_theme ) ? get_stylesheet_directory_uri().'/lib/jw_simple_options' : plugins_url( 'jw_simple_options', dirname( __FILE__ ) );
		wp_register_style( 'spectrum', $sdir.'/css/spectrum.css', '', '1.0.9' );
		wp_register_script( 'spectrum', $sdir.'/js/spectrum.js', array( 'jquery' ), '1.0.9' );

		wp_register_style( $this->prefix.'admin_css', $sdir.'/css/jw_simple_options.css', '', '1.0' );
		wp_register_script( $this->prefix.'admin_js', $sdir.'/js/jquery.jw_simple_options.js' , '', '1.0' );

	}

	/**
	 * Display user-end options page
	 */
	public function render_options_page() {

?>
        	<div class="wrap">
            	<div id="icon-options-general" class="icon32"><br /></div>
                <h2><?php echo $this->plugin_title; ?></h2>
                <p class="description">Options page powered by: <a href="https://github.com/JayWood/jw_simple_options" title="A simple, easy to configure, flexible, and open-source framework to make options pages on the fly.">JW Simple Options - ( Fork on Github )</a></p>
                <form method="post" action="options.php">
                <?php settings_fields( $this->prefix.'options' ); ?>
                <table class="form-table">
                	<tbody>
                    	<?php
		foreach ( $this->options as $k => $v ) {
			if ( $v['type'] == 'section' ) : ?>
                                <tr valign="top">
                                	<td colspan="2" class="jw_options_section"><h3 class="jw_options_section <?php echo $this->prefix.$k; ?>"><?php echo $v['name']; ?></h3></td>
                                </tr>
                                <?php else: ?>
							<tr valign="top">
								<th scope="row"><label for="<?php echo $this->prefix.$k; ?>"><?php echo $v['name']; ?></label></th>
                                <td><?php echo $this->render_option_field( $k, $v ); ?>
									<?php if ( isset( $v['desc'] ) ): ?>
                                        <p class="description"><?php echo $v['desc']; ?></p>
                                    <?php endif; ?>
                                </td>
							</tr>
                            <?php endif; ?>
							<?php
		}
?>
                    </tbody>
                </table>
                <p class="submit">
                	<input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes">
                </p>
                </form>
            </div>
        <?php
	}

	/**
	 * Display Spectrum color selection
	 *
	 * @param string  $key  Option identifier minus prefix.
	 * @param array   $data Associative array of field data with value in hex6 format.  ie. array('colorID' => '#FFFFFF')
	 */
	public function render_color_select( $key, $data ) {

		$opData = get_option( $this->prefix.$key, $data );

		$output = '<!-- Color Selects -->';
		foreach ( $opData as $k => $v ) {
			$output .= '<input type="text" id="'.$key.'_'.$k.'" name="'.$this->prefix.$key.'['.$k.']" value="'.$v.'" class="color_select">';
		}

		return $output;

	}

	/**
	 * Switch between options data types and display them.
	 *
	 * Offload rendering where necessary.
	 */
	public function render_option_field( $key, $data ) {
		switch ( $data['type'] ) {
		case 'text':
			$output = '<input type="text" name="'.$this->prefix.$key.'" id="'.$this->prefix.$key.'" value="'.strip_tags( get_option( $this->prefix.$key, $data['def'] ) ).'" class="regular-text" />';
			break;
		case 'password':
			$output = '<input type="password" name="'.$this->prefix.$key.'" id="'.$this->prefix.$key.'" value="'.get_option( $this->prefix.$key ).'" class="regular-text" />';
			break;
		case 'number':
			$output = '<input type="number" name="'.$this->prefix.$key.'" id="'.$this->prefix.$key.'" value="'.get_option( $this->prefix.$key, $data['def'] ).'" />';
			break;
		case 'data_array':
			$output = $this->buildDataArrayFields( $key, $data['fields'], $data['showhead'] );
			break;
		case 'select':
			$output = $this->buildSelectOptions( $key, $data['fields'], $data['def'] );
			break;
		case 'color':
			$output = $this->render_color_select( $key, $data['fields'] );
			break;
		case 'media':
			if ( function_exists( 'wp_enqueue_media' ) ) $output = $this->buildMediaOption( $key );
			break;
		case 'check':
			$output = $this->buildCheckFields( $key, $data['fields'], isset($data['def'])? $data['def'] : '' );
			break;
		case 'radio':
			$output = $this->buildRadioFields( $key, $data['fields'], $data['def'] );
			break;
		case 'textbox':
			$output = '<textarea name="'.$this->prefix.$key.'" id="'.$this->prefix.$key.'" rows="10" cols="50" class="large-text code" >'.get_option( $this->prefix.$key, $data['def'] ).'</textarea>';
			break;
		case 'timeframe':
			$output = $this->buildTimeframe( $key, $data['def'] );
			break;
		case 'editor':
			$opData = get_option( $this->prefix.$key, $data['def'] );
			$output = wp_editor( $opData, $this->prefix.$key, $data['settings'] );
			break;
		case 'section':
			$output = '<h3 class="jw_options_section '.$this->prefix.$key.'" id="'.$this->prefix.$key.'">'.$data['name'].'</h3>';
			break;
		default:
			$output = '<!-- Option ID: '.$key.'.'.$data['type'].' is not a valid option type. -->';
			break;
		}
		return $output;
	}

	/**
	 * Uninstalls any options.
	 *
	 * Needs to be called from functions.php
	 *
	 * @see register_uninstall_hook()
	 */
	public function uninstall() {
		if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) return false;
		// Remove options
		foreach ( $this->options as $k => $v ) delete_option( $this->prefix.$k );
	}

}