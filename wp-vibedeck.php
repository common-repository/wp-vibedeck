<?php
/*
Plugin Name: WP VibeDeck

Description: Embed the vibedeck audio player/store using a simple shortcode or widget.  
Vibedeck is a free audio and store to allow artists to sell their music online.  
After you create a free vibedeck account and upload your songs, this plugin will 
embed the player/store in your WordPress site.

Please note that this plugin is not affiliated with VibeDeck.  It is merely a plugin
intended to simplify the process of embedding VibeDeck in a WordPress site.

Version: 1.3
Author: Phillip Bryan
Author URI: http://www.theemeraldcurtain.com/
Plugin URL: http://www.theemeraldcurtain.com/wordpress-plugin/wp-vibedeck/
*/ 
 
/*  Copyright 2011  WP VibeDeck (email : phillip@theemeraldcurtain.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
define (WP_VIBEDECK_VERSION, 	'1.3');
define (WP_VIBEDECK_FULL, 		'default');
define (WP_VIBEDECK_WIDGET, 	'artwork');
define (WP_VIBEDECK_OPTIONS, 	'wp_vibedeck_options');

define (WP_VIBEDECK_DEFAULT_WIDTH, 	'520');
define (WP_VIBEDECK_DEFAULT_HEIGHT, '600');
define (WP_VIBEDECK_DEFAULT_WIDGET, '200');


//---------------------------------------------------------------------
// ACTIVATION
//---------------------------------------------------------------------
register_activation_hook( __FILE__, 'wp_vibedeck_activation' );
function wp_vibedeck_activation() {	
	$options = get_option(WP_VIBEDECK_OPTIONS);	
	if (false ==$options) {
		$options=array();	
		add_option(WP_VIBEDECK_OPTIONS, $options);
	}
}

//---------------------------------------------------------------------
// DEACTIVATION
//---------------------------------------------------------------------
register_deactivation_hook(__FILE__, 'wp_vibedeck_deactivation');
function wp_vibedeck_deactivation() {
    $options = get_option(WP_VIBEDECK_OPTIONS);
    if (is_array($options) && $options['uninstall'] === 'true') {        
		delete_option(WP_VIBEDECK_OPTIONS);		
    }
}

//---------------------------------------------------------------------
// ADMIN INIT
//---------------------------------------------------------------------
add_action( 'admin_init', 'wp_vibedeck_admin_init' );
function wp_vibedeck_admin_init() {

	register_setting( 'wp_vibedeck_group', WP_VIBEDECK_OPTIONS );
}


//---------------------------------------------------------------------
// SETTINGS PAGE
//---------------------------------------------------------------------
if ( is_admin() ){
	include('settings.php');
	add_action('admin_menu', 'wp_vibedeck_admin_menu');
	function wp_vibedeck_admin_menu() {
		add_options_page('WP VibeDeck',
			//'<img class="menu_pto" src="'.plugins_url('/images/menu-icon.ico', __FILE__).'" height="12" width="12" alt="" /> '.
			'WP VibeDeck', 'manage_options', 'wp_vibedeck_settings_page','wp_vibedeck_settings_page');
	}
}

//---------------------------------------------------------------------
// SHORTCODE
//---------------------------------------------------------------------
function print_wp_vibedeck($atts) {
	echo get_wp_vibedeck($atts);
}

add_shortcode("vibedeck", "get_wp_vibedeck");
function get_wp_vibedeck($atts) {
	
	/** GET PLUGIN SETTINGS **/    
	$options = get_option(WP_VIBEDECK_OPTIONS);
    if (!is_array($options)) $options=array();

	
	/** DETERMINE ARTIST AND ALBUM **/	
	$artist = $atts['artist'] ? $atts['artist'] : $options['artist'];
	$album = $atts['album'] ? $atts['album'] : $options['album'];
	
	if ('' == $atts['artist'] || '' == $atts['album']) {
		return '<div class="vibebox-wrapper"><p>'.
			('' == $artist ? 'VibeDeck artist is required.<br>' : '').
			('' == $album ? 'VibeDeck album is requird.' : '').
			'</p></div>';
	}	
	
	/** DETERMINE STYLE **/	
	$style = $atts['style'] ? $atts['style'] : WP_VIBEDECK_FULL;
	
	/** DETERMINE WIDTH **/	
	$width = $atts['width'] 
			? $atts['width'] 
			: (WP_VIBEDECK_FULL == $style ? WP_VIBEDECK_DEFAULT_WIDTH : WP_VIBEDECK_DEFAULT_WIDGET );
		
	/** DETERMINE HEIGHT **/	
	$height = $atts['height'] 
			? $atts['height'] 
			: (WP_VIBEDECK_FULL == $style ? WP_VIBEDECK_DEFAULT_HEIGHT : WP_VIBEDECK_DEFAULT_WIDGET );
		
	/** GENERATE THE CODE **/
	return '<div class="vibedeck-wrapper" ' .
			'style="width:' . $width . 'px; height:' . $height . 'px;overflow-x: hidden;' .
			'background:url(' . plugins_url('/images/loading.gif', __FILE__) . ') center center no-repeat;">' .
		'<script type="text/javascript" ' . 
			'src="http://vibedeck.com/frame/' . $artist .'/' . $album . '.js' .
				'?layout_settings%5Bstyle%5D=' . $style . 
				'&layout_settings%5Bbody_width%5D=' . $width . 'px' .
				'&layout_settings%5Bbody_height%5D=' . $height . 'px"></script>' .
        '</div>';





	/*
	return '<div class="vibedeck-wrapper"><iframe frameborder="0" src="http://vibedeck.com/frame/'.$atts['artist'].'/'.$atts['album'].'?layout_settings[body_height]='.$atts['height'].'px&amp;layout_settings[body_width]='.$atts['width'].'px&amp;layout_settings[style]='.$atts['style'].'" class="vibedeck-iframe" style="width: '.$atts['width'].'px; height: '.$atts['height'].'px; overflow-x: hidden; background:url('.plugins_url('/images/loading.gif', __FILE__).') center center no-repeat;">Sorry, but you need an iFrames capabable browser to view the store</iframe></div>';*/
}


//---------------------------------------------------------------------
// WIDGET
//---------------------------------------------------------------------
class WP_VibeDeck_Widget extends WP_Widget {
	function WP_VibeDeck_Widget() {
    	parent::WP_Widget( false, $name = 'WP-VibeDeck Widget' );
	}

	function widget( $args, $instance ) {
		extract( $args );
    	$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;

		if ($title) echo $before_title . $title . $after_title;
		
		$instance [style] = WP_VIBEDECK_WIDGET;
		echo get_wp_vibedeck ($instance);
		
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
    	return $new_instance;
	}

	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
		$width = esc_attr( $instance['width'] );
		$artist = esc_attr( $instance['artist'] );
		$album = esc_attr( $instance['album'] );
    
	?>

    <p> <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?>
    	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</label></p>
    <p> <label for="<?php echo $this->get_field_id( 'artist' ); ?>"><?php _e( 'Artist:' ); ?>
    	<input class="widefat" id="<?php echo $this->get_field_id( 'artist' ); ?>" name="<?php echo $this->get_field_name( 'artist' ); ?>" type="text" value="<?php echo $artist; ?>" />
		</label></p>
    <p> <label for="<?php echo $this->get_field_id( 'album' ); ?>"><?php _e( 'Album ID:' ); ?>
    	<input class="widefat" id="<?php echo $this->get_field_id( 'album' ); ?>" name="<?php echo $this->get_field_name( 'album' ); ?>" type="text" value="<?php echo $album; ?>" />
		</label></p>
    <p>	<label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width of artwork player:' ); ?>
    	<input class="widefat" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo $width; ?>" /></label></p>
  
    <?php
  }
}

add_action( 'widgets_init', 'wp_vibedeck_init' );
function wp_vibedeck_init() {
	register_widget( 'WP_VibeDeck_Widget' );
}

?>