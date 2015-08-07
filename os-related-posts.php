<?php
/**
 * Plugin Name: OS Related Posts
 * Plugin URI: http://offshorent.com/blog/extensions/os-related-posts
 * Description: The OS Related Posts feature scans all of your posts, analyzes them, and lets you show contextual posts under the same category.
 * Version: 2.0
 * Author: Jinesh, Senior Software Engineer
 * Author URI: http://www.offshorent.com/
 * Requires at least: 3.0
 * Tested up to: 4.2.3
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'OSRelatedPosts' ) ) :

/**
 * Main OSRelatedPosts Class
 *
 * @class OSRelatedPosts
 * @version	2.0
 */
final class OSRelatedPosts {
	
	/**
	* @var string
	* @since 2.0
	*/
	 
	public $version = '2.0';

	/**
	* @var OSRelatedPosts The single instance of the class
	* @since 2.0
	*/
	 
	protected static $_instance = null;

	/**
	* Main OSRelatedPosts Instance
	*
	* Ensures only one instance of OSRelatedPosts is loaded or can be loaded.
	*
	* @since 2.0
	* @static
	* @return OSRelatedPosts - Main instance
	*/
	 
	public static function init_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
	}

	/**
	* Cloning is forbidden.
	*
	* @since 2.0
	*/

	public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'os-related-posts' ), '2.0' );
	}

	/**
	* Unserializing instances of this class is forbidden.
	*
	* @since 2.0
	*/
	 
	public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'os-related-posts' ), '2.0' );
	}
        
	/**
	* Get the plugin url.
	*
	* @since 2.0
	*/

	public function plugin_url() {
        return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	* Get the plugin path.
	*
	* @since 2.0
	*/

	public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	* Get Ajax URL.
	*
	* @since 2.0
	*/

	public function ajax_url() {
        return admin_url( 'admin-ajax.php', 'relative' );
	}
        
	/**
	* OSRelatedPosts Constructor.
	* @access public
	* @return OSRelatedPosts
	* @since 2.0
	*/
	 
	public function __construct() {
		
        register_activation_hook( __FILE__, array( &$this, 'os_related_posts_install' ) );

        // Define constants
        self::os_related_posts_constants();

        // Include required files
        self::os_related_posts_admin_includes();

        // Action Hooks
        add_action( 'init', array( $this, 'os_related_posts_init' ), 0 );
        add_action( 'admin_init', array( $this, 'os_related_posts_admin_init' ) );
        add_action( 'admin_menu', array( $this, 'os_related_posts_add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'os_related_posts_admin_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'os_related_posts_frontend_styles' ) ); 

        add_filter( 'the_content', array( $this, 'os_related_posts_contents' ) );     
	}
        
	/**
	* Install OSRelatedPosts
	* @since 2.0
	*/
	 
	public function os_related_posts_install (){
		
        // Flush rules after install
        flush_rewrite_rules();

        // Redirect to welcome screen
        set_transient( '_os_related_posts_activation_redirect', 1, 60 * 60 );
	}
        
	/**
	* Define OSRelatedPosts Constants
	* @since 2.0
	*/
	 
	private function os_related_posts_constants() {
		
		define( 'WPRP_PLUGIN_FILE', __FILE__ );
		define( 'WPRP_PLUGIN_BASENAME', plugin_basename( dirname( __FILE__ ) ) );
		define( 'WPRP_PLUGIN_URL', plugins_url() . '/' . WPRP_PLUGIN_BASENAME );
		define( 'WPRP_VERSION', $this->version );
		define( 'WPRP_TEXT_DOMAIN', 'WPRP' );
		define( 'WPRP_PERMALINK_STRUCTURE', get_option( 'permalink_struture' ) ? '&' : '?' );
		
	}
        
	/**
	* includes admin defaults files
	*
	* @since 2.0
	*/
	 
	private function os_related_posts_admin_includes() {
	}
        
	/**
	* Init OSRelatedPosts when WordPress Initialises.
	* @since 2.0
	*/
	 
	public function os_related_posts_init() {
            
        self::os_related_posts_do_output_buffer();
	}
    

	/**
	* Clean all output buffers
	*
	* @since  2.0
	*/
	 
	public function os_related_posts_do_output_buffer() {
            
        ob_start( array( &$this, "os_related_posts_do_output_buffer_callback" ) );
	}

	/**
	* Callback function
	*
	* @since  2.0
	*/
	 
	public function os_related_posts_do_output_buffer_callback( $buffer ){
        return $buffer;
	}
	
	/**
	* Clean all output buffers
	*
	* @since  2.0
	*/
	 
	public function os_related_posts_flush_ob_end(){
        ob_end_flush();
	}
    
    /**
	* Add admin menu for os_related_posts blog
	*
	* @since  2.0
	*/
	 
	public function os_related_posts_add_admin_menu () {
		add_menu_page( 'Related Posts Settings', 'Related Posts', 'manage_options', 'os-related-posts', array( $this, 'os_related_posts_settings_admin_menu' ), '
dashicons-align-left' );
    	add_submenu_page( 'os-related-posts', 'About Offshorent', 'About', 'manage_options', 'about_developer', array( $this, 'about_os_related_posts_developer' ) );
	} 

	/**
	* about_os_related_posts_developer for os_related_posts blog
	*
	* @since  2.0
	*/

	public function about_os_related_posts_developer() {

		ob_start();
		?>
		<div class="wrap">
			<div id="dashboard-widgets">
				<h2><?php _e( 'About Offshorent' );?></h2> 
				<div class="postbox-container">
					<div class="meta-box-sortables ui-sortable">
						<h2><?php _e( "We build your team. We build your trust.." );?></h2>
						<img src="<?php echo WPRP_PLUGIN_URL;?>/images/about.jpg" alt="" width="524">
						<p><?php _e( "We are experts at building web and mobile products. And more importantly, we are committed to building your trust. We are a leading offshore outsourcing center that works primarily with digital agencies and software development firms. Offshorent was founded by U.S. based consultants specializing in software development and who built a reputation for identifying the very best off-shore outsourcing talent. We are now applying what we learned over the past ten years with a mission to become the world’s most trusted off-shore outsourcing provider." );?></p>
						<ul class="offshorent">
							<li><a href="http://offshorent.com/services" target="_blank"><?php _e( 'Services' );?></a></li>
							<li><a href="http://offshorent.com/our-work" target="_blank"><?php _e( 'Our Works' );?></a></li>
							<li><a href="http://offshorent.com/clients-speak" target="_blank"><?php _e( 'Testimonials' );?></a></li>
							<li><a href="http://offshorent.com/our-team" target="_blank"><?php _e( 'Our Team' );?></a></li>
							<li><a href="http://offshorent.com/process" target="_blank"><?php _e( 'Process' );?></a></li>
							<li><a href="http://offshorent.com/life-offshorent" target="_blank"><?php _e( 'Life @ Offshorent' );?></a></li>
							<li><a href="https://www.facebook.com/Offshorent" target="_blank"><?php _e( 'Facebook Page' );?></a></li>
							<li><a href="http://offshorent.com/blog" target="_blank"><?php _e( 'Blog' );?></a></li>
						</ul>
						<div class="clear"></div>
					</div>
					<div class="clear"></div>	
				</div>
				<div class="postbox-container">
					<div class="meta-box-sortables ui-sortable">
						<h2><?php _e( "Contact Us" );?></h2>
						<p><?php _e( "Email: " );?><a href="mailto:<?php _e( "info@offshorent.com" );?>"><?php _e( "info@offshorent.com" );?></a></p>
						<p><?php _e( "Project Support: " );?><a href="mailto:<?php _e( "project-support@offshorent.com" );?>"><?php _e( "project-support@offshorent.com" );?></a></p>
						<p><?php _e( "Phone - US Office: " );?><?php _e( "+1(484) 313 – 4264" );?></p>					
						<p><?php _e( "Phone - India: " );?><?php _e( "+91 484 – 2624225" );?></p>
						<div class="location-col">
							<b>Philadelphia / USA</b>
							<p>1150 1st Ave #501,<br> King Of Prussia,PA 19406<br> Tel: (484) 313 &ndash; 4264 <br>Email <a href="mailto:philly@offshorent.com">philly@offshorent.com</a></p>
						</div>
						<div class="location-col">
							<b>Chicago / USA</b>
							<p> 233 South Wacker Drive, Suite 8400,<br> Chicago, IL 60606<br> Tel: (312) 380 &ndash; 0775 <br>Email: <a href="mailto:chicago@offshorent.com">chicago@offshorent.com</a></p>
						</div>
						<div class="location-col">
							<b>California / USA</b>
							<p>17311 Virtuoso. #102 Irvine,<br> CA 92620 <br>Tel: +1 949 391 1012 <br>Email: <a href="mailto:california@offshorent.com">california@offshorent.com</a></p>
						</div>
						<div class="location-col">
							<b>Sydney / AUSTRALIA</b>
							<p>Suite 59, 38 Ricketty St, Mascot,<br> New South Wales &ndash; 2020,<br> Sydney, Australia,<br> Tel: 02 8011 3413 <br>Email: <a href="mailto:sydney@offshorent.com">sydney@offshorent.com</a></p>
						</div>
						<div class="location-col">
							<b>Cochin / INDIA</b>
							<p>Palm Lands, 3rd Floor,<br> Temple Road, Bank Jn,<br> Aluva &ndash; 01, Cochin, Kerala <br>Tel: +91 484 &ndash; 2624225 <br>Email: <a href="mailto:aluva@offshorent.com">aluva@offshorent.com</a></p>
						</div>	
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="social">
				<img src="<?php echo WPRP_PLUGIN_URL;?>/images/social.png" usemap="#av92444" width="173" height="32" alt="click map" border="0" />
				<map id="av92444" name="av92444">
					<!-- Region 1 -->
					<area shape="rect" alt="Facebook" title="Facebook" coords="1,2,29,30" href="https://www.facebook.com/Offshorent" target="_blank" />
					<!-- Region 2 -->
					<area shape="rect" alt="Twitter" title="Twitter" coords="36,1,64,31" href="https://twitter.com/Offshorent" target="_blank" />
					<!-- Region 3 -->
					<area shape="rect" alt="Google" title="Google" coords="73,3,98,29" href="https://plus.google.com/+Offshorent/posts" target="_blank" />
					<!-- Region 4 -->
					<area shape="rect" alt="Linkedin" title="Linkedin" coords="110,1,136,30" href="https://www.linkedin.com/company/offshorent" target="_blank" />
					<!-- Region 5 -->
					<area shape="rect" alt="Youtube" title="Youtube" coords="145,3,169,31" href="http://www.youtube.com/user/Offshorent" target="_blank" />
					<area shape="default" nohref alt="" />
				</map>
			</div>			
		</div>
		<?php

		//return ob_get_contents();
	}

	/**
	* Setting function for os_related_posts blog
	*
	* @since  2.0
	*/
	 
	public function os_related_posts_settings_admin_menu () {

		ob_start();

		$options = get_option( 'related_posts_settings' );
			
		// General option values
		$rp_page_title = isset( $options['rp_page_title'] ) ? esc_attr( $options['rp_page_title'] ) : 'Related Posts';
		$posts_per_page = isset( $options['posts_per_page'] ) ? esc_attr( $options['posts_per_page'] ) : 4;
		$display_type = isset( $options['display_type'] ) ? esc_attr( $options['display_type'] ) : 'normal';			
		
		// Heading option values
		$font_family = isset( $options['font_family'] ) ? esc_attr( $options['font_family'] ) : 'Open Sans';
		$heading_font_size = isset( $options['heading_font_size'] ) ? esc_attr( $options['heading_font_size'] ) : '18px';
		$heading_font_color = isset( $options['heading_font_color'] ) ? esc_attr( $options['heading_font_color'] ) : '#000000';			
		$heading_font_hover_color = isset( $options['heading_font_hover_color'] ) ? esc_attr( $options['heading_font_hover_color'] ) : '#cccccc';		
		$content_font_size = isset( $options['content_font_size'] ) ? esc_attr( $options['content_font_size'] ) : '14px';
		$content_font_color = isset( $options['content_font_color'] ) ? esc_attr( $options['content_font_color'] ) : '#999999';
		
		// Color option values
		$bg_color = isset( $options['bg_color'] ) ? esc_attr( $options['bg_color'] ) : '#f0e7cd';
		$border_color = isset( $options['border_color'] ) ? esc_attr( $options['border_color'] ) : '#f6c542';		
		?>
		<div class="wrap">
			<h2><?php _e( 'Related Posts Blog Settings' );?></h2>           
			<form method="post" action="options.php">
				<?php settings_fields( 'related_posts' ); ?>
                <div class="form-table">
                	<div class="form-widefat">
					    <h3>General Settings</h3>
					    <div class="row-table">
					        <label>Related Post Title: </label>
					        <input type="text" name="related_posts_settings[rp_page_title]" value="<?php echo esc_attr( $rp_page_title );?>">
					        <div class="clear"></div>
					    </div>
					    <div class="row-table">
					        <label>Posts Per Page: </label>
					        <select name="related_posts_settings[posts_per_page]">
					            <?php for( $i = 2; $i < 5; $i++ ) { ?> 
					            <option value="<?php echo $i;?>" <?php selected( $posts_per_page, $i ); ?>><?php echo $i;?></option>
					            <?php } ?>
					        </select>
					        <div class="clear"></div>
					    </div>	
					    <div class="row-table">
					        <label>Display Type: </label>
					        <select name="related_posts_settings[display_type]">					            
					            <option value="normal" <?php selected( $display_type, 'normal' ); ?>>Normal</option>					           
					            <option value="slider" <?php selected( $display_type, 'slider' ); ?>>Slider</option>
					            <option value="listing" <?php selected( $display_type, 'listing' ); ?>>Listing</option>
					        </select>
					        <div class="clear"></div>
					    </div>				    
					</div>
					<div class="form-widefat">
						<h3>Content Settings</h3>
						<div class="row-table-half">
							<div class="row-table">
						        <label>Font Family: </label>
						        <select id="font_family" name="related_posts_settings[font_family]">
						            <option value="Arial" <?php selected( $font_family, 'Arial' ); ?>>Arial</option>
						            <option value="Verdana" <?php selected( $font_family, 'Verdana' ); ?>>Verdana</option>
						            <option value="Helvetica" <?php selected( $font_family, 'Helvetica' ); ?>>Helvetica</option>
						            <option value="Comic Sans MS" <?php selected( $font_family, 'Comic Sans MS' ); ?>>Comic Sans MS</option>
						            <option value="Georgia" <?php selected( $font_family, 'Georgia' ); ?>>Georgia</option>
						            <option value="Trebuchet MS" <?php selected( $font_family, 'Trebuchet MS' ); ?>>Trebuchet MS</option>
						            <option value="Times New Roman" <?php selected( $font_family, 'Times New Roman' ); ?>>Times New Roman</option>
						            <option value="Tahoma" <?php selected( $font_family, 'Tahoma' ); ?>>Tahoma</option>
						            <option value="Oswald" <?php selected( $font_family, 'Oswald' ); ?>>Oswald</option>
						            <option value="Open Sans" <?php selected( $font_family, 'Open Sans' ); ?>>Open Sans</option>
						            <option value="Fontdiner Swanky" <?php selected( $font_family, 'Fontdiner Swanky' ); ?>>Fontdiner Swanky</option>
						            <option value="Crafty Girls" <?php selected( $font_family, 'Crafty Girls' ); ?>>Crafty Girls</option>
						            <option value="Pacifico" <?php selected( $font_family, 'Pacifico' ); ?>>Pacifico</option>
						            <option value="Satisfy" <?php selected( $font_family, 'Satisfy' ); ?>>Satisfy</option>
						            <option value="Gloria Hallelujah" <?php selected( $font_family, 'TGloria Hallelujah' ); ?>>TGloria Hallelujah</option>
						            <option value="Bangers" <?php selected( $font_family, 'Bangers' ); ?>>Bangers</option>
						            <option value="Audiowide" <?php selected( $font_family, 'Audiowide' ); ?>>Audiowide</option>
						            <option value="Sacramento" <?php selected( $font_family, 'Sacramento' ); ?>>Sacramento</option>
						        </select>
						        <div class="clear"></div>
						    </div>                            
							<div class="row-table">
						        <label>Heading Font Size: </label>
						        <select name="related_posts_settings[heading_font_size]">
						            <?php for( $i = 16; $i < 33; $i++ ) { ?> 
						            <option value="<?php echo $i;?>px" <?php selected( $heading_font_size, $i . 'px' ); ?>><?php echo $i;?>px</option>
						            <?php } ?>
						        </select>
						        <div class="clear"></div>
						    </div>
						    <div class="row-table">
						        <label>Heading Font Color: </label>
						        <input type="color" name="related_posts_settings[heading_font_color]" value="<?php echo $heading_font_color;?>" class="small" />
						        <div class="clear"></div>
						    </div>
						</div>
						<div class="row-table-half">    
						    <div class="row-table">
						        <label>Heading Hover Color: </label>
						        <input type="color" name="related_posts_settings[heading_font_hover_color]" value="<?php echo $heading_font_hover_color;?>" class="small" />
						        <div class="clear"></div>
						    </div>  
						    <div class="row-table">
						        <label>Content Font Size: </label>
						        <select name="related_posts_settings[content_font_size]">
						            <?php for( $j = 10; $j < 21; $j++ ) { ?> 
						            <option value="<?php echo $j;?>px" <?php selected( $content_font_size, $j . 'px' ); ?>><?php echo $j;?>px</option>
						            <?php } ?>
						        </select>
						        <div class="clear"></div>
						    </div>
						    <div class="row-table">
						        <label>Content Font Color: </label>
						        <input type="color" name="related_posts_settings[content_font_color]" value="<?php echo $content_font_color;?>" class="small" />
						        <div class="clear"></div>
						    </div>    
					    </div>                        
					</div>
					<div class="form-widefat">
						<h3>Background Color Settings</h3>   
					    <div class="row-table">
					        <label>Background Color: </label>
					        <input type="color" name="related_posts_settings[bg_color]" value="<?php echo $bg_color;?>" class="small" />
					        <div class="clear"></div>
					    </div>
					    <div class="row-table">
					        <label>Border Color: </label>
					        <input type="color" name="related_posts_settings[border_color]" value="<?php echo $border_color;?>" class="small" />
					        <div class="clear"></div>
					    </div>					                          
					</div>
                </div>	                				
				<?php submit_button(); ?>
			</form>
		</div>
		<?php 

		return ob_get_contents();
	}  

	/**
	* Admin init OSRelatedPosts when WordPress Initialises.
	* @since  2.0
	*/
	 
	public function os_related_posts_admin_init() {

		register_setting(
			'related_posts', // Option group
			'related_posts_settings', // Option name
			array( &$this, 'sanitize' ) // Sanitize
		);
	}
    
    /**
	* Sanitize each setting field as needed
	* @since 2.0
	*/
		 
	public function sanitize( $input ) {
		
		 
		$new_input = array();
		
		// General Settings option values			
		if( isset( $input['rp_page_title'] ) )
			$new_input['rp_page_title'] = sanitize_text_field( $input['rp_page_title'] );

		if( isset( $input['posts_per_page'] ) )
			$new_input['posts_per_page'] = sanitize_text_field( $input['posts_per_page'] );

		if( isset( $input['display_type'] ) )
			$new_input['display_type'] = sanitize_text_field( $input['display_type'] );	
				
			
		// Heading Settings option values

		if( isset( $input['font_family'] ) )
			$new_input['font_family'] = sanitize_text_field( $input['font_family'] );

		if( isset( $input['heading_font_size'] ) )
			$new_input['heading_font_size'] = sanitize_text_field( $input['heading_font_size'] );
			
		if( isset( $input['heading_font_color'] ) )
			$new_input['heading_font_color'] = sanitize_text_field( $input['heading_font_color'] );	
			
		if( isset( $input['heading_font_hover_color'] ) )
			$new_input['heading_font_hover_color'] = sanitize_text_field( $input['heading_font_hover_color'] );
							
		if( isset( $input['content_font_size'] ) )
			$new_input['content_font_size'] = sanitize_text_field( $input['content_font_size'] );
			
		if( isset( $input['content_font_color'] ) )
			$new_input['content_font_color'] = sanitize_text_field( $input['content_font_color'] );	
		

		// Background Color Settings option values	
		if( isset( $input['bg_color'] ) )
			$new_input['bg_color'] = sanitize_text_field( $input['bg_color'] );
			
		if( isset( $input['border_color'] ) )
			$new_input['border_color'] = sanitize_text_field( $input['border_color'] );		
		
			
		return $new_input;
	}

	/**
	* admin style hook for timelineBlog
	*
	* @since  1.0
	*/
	 
	public function os_related_posts_admin_styles() {	

        wp_enqueue_style( 'admin-style', plugins_url( 'css/admin/style.css', __FILE__ ) );    
	}

	/**
	* Frontend style hook for OSRelatedPosts
	*
	* @since  2.0
	*/
	 
	public function os_related_posts_frontend_styles() {

		if( !is_admin() ){

			$options = get_option( 'related_posts_settings' );

			// General option values
			$rp_page_title = isset( $options['rp_page_title'] ) ? esc_attr( $options['rp_page_title'] ) : '';
			$posts_per_page = isset( $options['posts_per_page'] ) ? esc_attr( $options['posts_per_page'] ) : '';
			$display_type = isset( $options['display_type'] ) ? esc_attr( $options['display_type'] ) : '';

			// Heading option values
			$font_family = isset( $options['font_family'] ) ? esc_attr( $options['font_family'] ) : '';
			$heading_font_size = isset( $options['heading_font_size'] ) ? esc_attr( $options['heading_font_size'] ) : '';
			$heading_font_color = isset( $options['heading_font_color'] ) ? esc_attr( $options['heading_font_color'] ) : '';         
			$heading_font_hover_color = isset( $options['heading_font_hover_color'] ) ? esc_attr( $options['heading_font_hover_color'] ) : '';
			$content_font_size = isset( $options['content_font_size'] ) ? esc_attr( $options['content_font_size'] ) : '';
			$content_font_color = isset( $options['content_font_color'] ) ? esc_attr( $options['content_font_color'] ) : '';

			// Color option values
			$bg_color = isset( $options['bg_color'] ) ? esc_attr( $options['bg_color'] ) : '';
			$border_color = isset( $options['border_color'] ) ? esc_attr( $options['border_color'] ) : '';

			/* calculations */ 
			if( $display_type === 'listing' ) {
				$width = 100;
			} else {
				$width = ( 100 - ( absint( $posts_per_page ) - 1 ) * 2 ) / absint( $posts_per_page );
			}	
			$min_height = ( absint( $posts_per_page ) == 4 ) ? '215px' : '' ;

			$custom_css = ".wprp_slider{
								font-family: " . $font_family . ";
							}
							.wprp_slider ul li {
								width: " . $width . "%;
							}
							.wprp_slider ul li .wprp_content_area {
								Background: " . $bg_color . ";
								border: 1px solid " . $border_color . ";
								min-height: " . $min_height . ";
							}
							.wprp_slider ul li .wprp_content_area h3 a {
								font-size: " . $heading_font_size . ";
								color: " . $heading_font_color . ";
							}
							.wprp_slider ul li .wprp_content_area h3 a:hover {
								color: " . $heading_font_hover_color . ";
							}
							.wprp_slider ul li .wprp_content_area p {
								font-size: " . $content_font_size . ";
								color: " . $content_font_color . ";
							}
							.wprp_slider ul li .wprp_content_area a.readmore {
								color: " . $heading_font_color . ";
							}
							.wprp_slider ul li .wprp_content_area a.readmore:hover {
								color: " . $heading_font_hover_color . ";
							}
							.wprp_slider ul.listing li {
								Background: " . $bg_color . ";
								border: 1px solid " . $border_color . ";								
							}
							.wprp_slider ul.listing li h3 a {
								font-size: " . $heading_font_size . ";
								color: " . $heading_font_color . ";								
							}
							.wprp_slider ul.listing li h3 a:hover {
								color: " . $heading_font_hover_color . ";
							}
							.wprp_slider ul li:nth-child(" . ( $posts_per_page + 1 ) . ") {
							    margin-left: 0; 
							}";

	        wp_enqueue_style( 'frontend-fonts', 'http://fonts.googleapis.com/css?family=Oswald|Open+Sans|Fontdiner+Swanky|Crafty+Girls|Pacifico|Satisfy|Gloria+Hallelujah|Bangers|Audiowide|Sacramento' );              
	        wp_enqueue_style( 'bxslider-style', plugins_url( 'bxslider/jquery.bxslider.css', __FILE__ ) );
	        wp_enqueue_script( 'bxslider-js', plugins_url( 'bxslider/jquery.bxslider.min.js', __FILE__ ), array(), '1.0.0', true );
	        wp_enqueue_style( 'frontend-style', plugins_url( 'css/wprp-style.css', __FILE__ ) ); 
	        wp_add_inline_style( 'frontend-style', $custom_css ); 
        }   
	}

	function os_related_posts_contents( $html_content ) {

		global $post;

		$options = get_option( 'related_posts_settings' );
			
		// General option values
		$rp_page_title = isset( $options['rp_page_title'] ) ? esc_attr( $options['rp_page_title'] ) : ''; 
		$posts_per_page = isset( $options['posts_per_page'] ) ? esc_attr( $options['posts_per_page'] ) : '';
		$display_type = isset( $options['display_type'] ) ? esc_attr( $options['display_type'] ) : '';				
		
		// Content option values
		$font_family = isset( $options['font_family'] ) ? esc_attr( $options['font_family'] ) : '';
		$heading_font_size = isset( $options['heading_font_size'] ) ? esc_attr( $options['heading_font_size'] ) : '';
		$heading_font_color = isset( $options['heading_font_color'] ) ? esc_attr( $options['heading_font_color'] ) : '';			
		$heading_font_hover_color = isset( $options['heading_font_hover_color'] ) ? esc_attr( $options['heading_font_hover_color'] ) : '';
		$content_font_size = isset( $options['content_font_size'] ) ? esc_attr( $options['content_font_size'] ) : '';
		$content_font_color = isset( $options['content_font_color'] ) ? esc_attr( $options['content_font_color'] ) : '';
		
		// Color option values
		$first_bg_color = isset( $options['first_bg_color'] ) ? esc_attr( $options['first_bg_color'] ) : '';
		$first_border_color = isset( $options['first_border_color'] ) ? esc_attr( $options['first_border_color'] ) : '';

	    if( is_single() ) {

			$categories = get_the_category();

			if ( $categories ) {

				foreach ( $categories as $category ) {
					$cat .= $category->term_id . ', ';
				}
					
				$args = array(
								'cat' => rtrim( $cat, ', ' ),
								'post__not_in' => array( $post->ID ),
								'posts_per_page'=> 10
							);

				$my_query = null;
				$my_query = new WP_Query( $args );
				
				$size = ( absint( $posts_per_page ) == 4 ) ? 'thumbnail' : 'medium' ;
				$count = ( absint( $posts_per_page ) == 4 ) ? 50 : 90 ;


				$html_content .= '<div class="wprp_slider"><h2>' . $rp_page_title . '</h2><ul class="' . $display_type . '">';

				if( $my_query->have_posts() ) {

					while ( $my_query->have_posts() ) : $my_query->the_post();

						$content = substr( strip_tags( $post->post_content ), 0, $count );

						if( $display_type === 'normal' || $display_type === 'slider' ) {
							$html_content .= 	'<li>
													<div class="wprp_image_area">
														<a href="' . get_the_permalink( $post->ID ) . '" rel="bookmark" title="' . get_the_title( $post->ID ) . '">
															' . get_the_post_thumbnail( $post->ID, $size ) . '
														</a>
														<div class="clear"></div>
													</div>
													<div class="wprp_content_area">
														<h3>
															<a href="' . get_the_permalink( $post->ID ) . '" rel="bookmark" title="' . get_the_title( $post->ID ) . '">
																' . get_the_title( $post->ID ) . '
															</a>
														</h3>
														<p>' . $content . '</p>														
														<a class="readmore" href="' . get_the_permalink( $post->ID ) . '">Read More >></a>
														<div class="clear"></div>
													</div>
													<div class="clear"></div>
												</li>';

						} else {
							$html_content .= 	'<li>
													<h3>
														<a href="' . get_the_permalink( $post->ID ) . '" rel="bookmark" title="' . get_the_title( $post->ID ) . '">
															' . get_the_title( $post->ID ) . '
														</a>
													</h3>
												</li>';
						}
					endwhile;
				}
				$html_content .= '</ul></div>';

				if( $display_type === 'slider' ) {

					if( absint( $posts_per_page ) == 4 ) {
						$width = 150;
					} elseif( absint( $posts_per_page ) == 3 ) {
						$width = 190;
					} else {
						$width = 300;
					}	
					$html_content .= 	'<script type="text/javascript">
										jQuery( document ).ready(function( $ ){
											$( ".slider" ).bxSlider({
										 		minSlides: 1,
										  		maxSlides: ' . absint( $posts_per_page ) . ',
										  		slideWidth: ' . $width . ',
										  		slideMargin: 10,
										  		pager: false
											});
										});
										</script>';
				}
				
			}
			wp_reset_postdata();
		}

	   return $html_content;
	}
}

endif;

/**
 * Returns the main instance of OSRelatedPosts to prevent the need to use globals.
 *
 * @since  2.0
 * @return OSRelatedPosts
 */
 
return new OSRelatedPosts;