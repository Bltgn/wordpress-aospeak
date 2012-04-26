<?php
/**
Plugin Name: AO Speak for Wordpress
Plugin URI: http://boltgun.the-kindred.info
Description: Display the online list from the AO Speak server for Teamspeak.
Author: Guillaume Olivetti
Version: 0.1
Author URI: http://devduweb.com

License: GPL2

Copyright 2011  Guillaume Olivetti  (email : contact@devduweb.com)

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

@package AOSpeak
@version 0.1
@since 3.2.1

*/

// Includes
require_once dirname(__FILE__).'/view.php';

/*
 * Configuration
 */
// If set to FALSE, doesn't cache results.
define('AO_SPEAK_CACHE_ACTIVATE', FALSE);
// Cache's directory. Must be writeable.
define('AO_SPEAK_CACHE_DIR', dirname(__FILE__).'/cache/');
// Cache's timeout time in minute
define('AO_SPEAK_CACHE_TIMEOUT', 5);
// Key word for translation support
define('AO_SPEAK_I18N_DOMAIN', 'aospeak_plugin');
// Version string
define('AO_SPEAK_VERSION', '0.1');
// Add the JS to the footer instead of header
define('AO_SPEAK_JS_FOOTER', FALSE);

/*
 * Actions
 */
// Widget registration
add_action( 'widgets_init', create_function( '', 'register_widget("Ao_Speak_Widget");' ) );
add_action( 'wp_enqueue_scripts', 'aospeak_enqueue_javascript' );

/**
 * The widget, extends WP Widget management.
 * Will either display a cached result or create a javascript request.
 *
 * @see Ao_Speak_View
 */
class Ao_Speak_Widget extends WP_Widget {

	/**
	 * Widget setup
	 *
	 * @see WP_Widget::__construct()
	 */
	public function __construct() {
		parent::__construct(
				'aospeak_widget',
				'AOSpeak',
				array( 'description' => 'Displays the status of an AOSpeak channel' )
			);
	}

	/**
	 * Displays the widget on the site.
	 * If activated, first check for the cache.
	 * If the cache returns data, display it.
	 * Otherwise, displays an the empty space for the AJAX action.
	 *
	 * @param array $args
	 * @param array $instance
	 * @see WP_Widget::widget
	 */
	public function widget( $args, $instance ) {
		extract($args);

		// Filtering
		$title = apply_filters( 'widget_title', $instance['title'] );

		// Output
		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		
		// Cache check
		
		// No cache => request
		$view = new Ao_Speak_View_Request;
		$view->setData( 'instance', $instance )
			->setData( 'widget_id', $args['widget_id'] );
		echo $view;

		echo $after_widget;
	}

	/**
	 * Updates the plugin's settings.
	 * First validate the selected mode and associated settings.
	 * Saves the appropriate settings.
	 * @todo Error messages, not just silent rejects
	 * @todo Cache check and direct display
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 * @see WP_Widget::update
	 */
	public function update( $new_instance, $old_instance ) {

		// Title
		$instance['title'] = strip_tags($new_instance['title']);

		// Mode
		if( empty( $new_instance['mode'] ) or FALSE === in_array( $new_instance['mode'], array( 1, 2 ) ) ) {
			$instance['mode'] = 1;
		} else {
			$instance['mode'] = (int) $new_instance['mode'];
		}
		
		// Dimension
		if( empty( $new_instance['dim'] ) or FALSE === in_array( $new_instance['dim'], array( 1, 2 ) ) ) {
			$instance['dim'] = 1;
		} else {
			$instance['dim'] = (int) $new_instance['dim'];
		}

		// For each mode select and validate the options
		if($instance['mode'] === 2) {
			$instance['org'] = ( empty( $new_instance['org'] ) ) ? 0 : (int) $new_instance['org'];
		}

		return $instance;
	}

	/**
	 * Displays a setup form.
	 * The form is made of 3 parts :
	 * - Mode selection
	 * - Online settings
	 * - Org settings.
	 * Only the settings associated with the selected mode is displayed.
	 * @todo Help tootip
	 *
	 * @param array $instance
	 * @see WP_Widget::form
	 */
	public function form( $instance ) {
		// Init
		$default = array(
			'title' => __( 'Who is online on AOSpeak', AO_SPEAK_I18N_DOMAIN),
			'mode' => 1, 
			'dim' => 1, 
			'org' => 0
		);
		$instance = wp_parse_args( (array) $instance, $default );
		
		// Title field
		echo '<label for="' . $this->get_field_id( 'title' ) . '">' . __( 'Title:', AO_SPEAK_I18N_DOMAIN ) . '</label>
			<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $instance['title'] . '">';
		
		// Mode select
		echo '<label for="' . $this->get_field_id( 'mode' ).'">'.__( 'Mode:', AO_SPEAK_I18N_DOMAIN ) . '</label>
			<select id="' . $this->get_field_id( 'mode' ).'" name="'.$this->get_field_name( 'mode' ) . '" class="widefat" style="width:100%;">
				<option value="1" ', ( '1' == $instance['mode'] ) ? 'selected="selected"' : '', '>' . __( 'Online', AO_SPEAK_I18N_DOMAIN  ) . '</option>
				<option value="2" ', ( '2' == $instance['mode'] ) ? 'selected="selected"' : '', '>' . __( 'Organization', AO_SPEAK_I18N_DOMAIN  ) . '</option>
			</select>';
		
		// Dimension select
		echo '<label for="' . $this->get_field_id( 'dim' ).'">'.__( 'Dimension:', AO_SPEAK_I18N_DOMAIN ).'</label>
			<select id="' . $this->get_field_id( 'dim' ).'" name="'.$this->get_field_name( 'dim' ) . '" class="widefat" style="width:100%;">
				<option  value="0" ', ( '0' == $instance['dim'] ) ? 'selected="selected"' : '', '>'. __( 'Any', AO_SPEAK_I18N_DOMAIN ) . '</option>
				<option  value="1" ', ( '1' == $instance['dim'] ) ? 'selected="selected"' : '', '>'. __( 'Atlantean', AO_SPEAK_I18N_DOMAIN ) . '</option>
				<option  value="2" ', ( '2' == $instance['dim'] ) ? 'selected="selected"' : '', '>' . __( 'Rimor', AO_SPEAK_I18N_DOMAIN ) . '</option>
			</select>';
		
		// Org id
		echo '<label for="' . $this->get_field_id( 'org' ) . '">' . __( 'Org ID:', AO_SPEAK_I18N_DOMAIN ) . '</label>
			<input class="widefat" id="' . $this->get_field_id( 'org' ) . '" name="' . $this->get_field_name( 'org' ) . '" type="text" value="' . $instance['org'] . '">';
		
		// Fields (ajouter checked)
		echo '<h5>' . __('Fields to display') . '</h5>
			<input type="checkbox" id="' . $this->get_field_id( 'fieldName' ) . '">
			<label for="' . $this->get_field_id( 'fieldName' ) . '">' . __('Name') . '</label>
			<input type="checkbox" id="' . $this->get_field_id( 'fieldName' ) . '">
			<label for="' . $this->get_field_id( 'fieldCountry' ) . '">' . __('Country') . '</label>
			<input type="checkbox" id="' . $this->get_field_id( 'fieldName' ) . '">
			<label for="' . $this->get_field_id( 'fieldIngame' ) . '">' . __('Ingame') . '</label><br>
			<input type="checkbox" id="' . $this->get_field_id( 'fieldName' ) . '">
			<label for="' . $this->get_field_id( 'fieldIdle' ) . '">' . __('Idle Time') . '</label>';
		
	}

}

/**
 * Adds the script to the WP installation
 */
function aospeak_enqueue_javascript() {
	wp_enqueue_script( 'jquery' );
    wp_enqueue_script(
			'aospeak',
			plugins_url( 'aospeak.js' , __FILE__ ),
			array( 'jquery' ),
			AO_SPEAK_VERSION,
			AO_SPEAK_JS_FOOTER
		);
	// Add the url to the request handler
	wp_localize_script( 'aospeak', 'aospeak_setup', array( 'url' => plugins_url( 'request.php' , __FILE__ ) ) );
}   