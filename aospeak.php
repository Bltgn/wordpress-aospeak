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

/*
 * Actions
 */
// Widget registration
add_action( 'widgets_init', create_function( '', 'register_widget("Ao_Speak_Widget");' ) );

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
		$title = apply_filters( 'widget_title', $instance['title'] );

		// Output
		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		
		// Request here
		echo '<p>toto</p>';

		echo $after_widget;
	}

	/**
	 * Updates the plugin's settings.
	 * First validate the selected mode and associated settings.
	 * Saves the appropriate settings.
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 * @see WP_Widget::update
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Title
		$instance['title'] = strip_tags($new_instance['title']);

		// Mode validation

		// For each mode select and validate the options

		return $instance;
	}

	/**
	 * Displays a setup form.
	 * The form is made of 3 parts :
	 * - Mode selection
	 * - Online settings
	 * - Org settings.
	 * Only the settings associated with the selected mode is displayed.
	 *
	 * @param array $instance
	 * @see WP_Widget::form
	 */
	public function form( $instance ) {
		// Settings filtering
		$title = ( $instance ) ? esc_attr( $instance[ 'title' ] ) : __( 'Who is online on AOSpeak', AO_SPEAK_I18N_DOMAIN);
		
		// Title field
		echo '<label for="' . $this->get_field_id('title') . '">' . __('Title:') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '">';
	}

}
