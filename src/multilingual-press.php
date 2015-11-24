<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name: MultilingualPress
 * Plugin URI:  https://wordpress.org/plugins/multilingual-press/
 * Description: Create a fast translation network on WordPress multisite. Run each language in a separate site, and connect the content in a lightweight user interface. Use a customizable widget to link to all sites.
 * Author:      Inpsyde GmbH
 * Author URI:  http://inpsyde.com
 * Version:     2.3.0-alpha
 * Text Domain: multilingualpress
 * Domain Path: /languages
 * License:     GPLv3
 * Network:     true
 */

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'Multilingual_Press' ) ) {
	require plugin_dir_path( __FILE__ ) . 'inc/Multilingual_Press.php';
}

// Kick-Off
add_action( 'plugins_loaded', 'mlp_init', 0 );

/**
 * Initialize the plugin.
 *
 * @wp-hook plugins_loaded
 *
 * @return void
 */
function mlp_init() {

	global $pagenow, $wp_version, $wpdb;

	$plugin_path = plugin_dir_path( __FILE__ );
	$plugin_url = plugins_url( '/', __FILE__ );

	$assets_base = 'assets';

	if ( ! class_exists( 'Mlp_Load_Controller' ) ) {
		require $plugin_path . 'inc/autoload/Mlp_Load_Controller.php';
	}

	$loader = new Mlp_Load_Controller( $plugin_path . 'inc' );

	$data = new Mlp_Plugin_Properties();

	$data->set( 'loader', $loader->get_loader() );

	$locations = new Mlp_Internal_Locations();
	$locations->add_dir( $plugin_path, $plugin_url, 'plugin' );
	$assets_locations = array(
		'css'    => 'css',
		'js'     => 'js',
		'images' => 'images',
		'flags'  => 'images/flags',
	);
	foreach ( $assets_locations as $type => $dir ) {
		$locations->add_dir(
			$plugin_path . $assets_base . '/' . $dir,
			$plugin_url . $assets_base . '/' . $dir,
			$type
		);
	}
	$data->set( 'locations', $locations );

	$data->set( 'plugin_file_path', __FILE__ );
	$data->set( 'plugin_base_name', plugin_basename( __FILE__ ) );

	$headers = get_file_data(
		__FILE__,
		array(
			'text_domain_path' => 'Domain Path',
			'plugin_uri'       => 'Plugin URI',
			'plugin_name'      => 'Plugin Name',
			'version'          => 'Version',
		)
	);
	foreach ( $headers as $name => $value ) {
		$data->set( $name, $value );
	}

	if ( ! mlp_pre_run_test( $pagenow, $data, $wp_version, $wpdb ) ) {
		return;
	}

	$mlp = new Multilingual_Press( $data, $wpdb );
	$mlp->setup();
}

/**
 * Check current state of the WordPress installation.
 *
 * @param  string                          $pagenow
 * @param  Inpsyde_Property_List_Interface $data
 * @param  string                          $wp_version
 * @param  wpdb                            $wpdb
 *
 * @return bool
 */
function mlp_pre_run_test( $pagenow, Inpsyde_Property_List_Interface $data, $wp_version, wpdb $wpdb ) {

	$self_check = new Mlp_Self_Check( __FILE__, $pagenow );
	$requirements_check = $self_check->pre_install_check(
		$data->get( 'plugin_name' ),
		$data->get( 'plugin_base_name' ),
		$wp_version
	);

	if ( Mlp_Self_Check::PLUGIN_DEACTIVATED === $requirements_check ) {
		return FALSE;
	}

	$site_relations_schema = new Mlp_Site_Relations_Schema( $wpdb );
	$data->set( 'site_relations_schema', $site_relations_schema );
	$data->set( 'site_relations', new Mlp_Site_Relations( $wpdb, $site_relations_schema ) );

	$relationships_schema = new Mlp_Relationships_Schema( $wpdb );
	$data->set( 'relationships_schema', $relationships_schema );

	$content_relations_schema = new Mlp_Content_Relations_Schema( $wpdb );
	$data->set( 'content_relations_schema', $content_relations_schema );
	$data->set(
		'content_relations',
		new Mlp_Content_Relations(
			$wpdb,
			$content_relations_schema,
			$relationships_schema
		)
	);
	$data->set( 'content_relations_table', $content_relations_schema->get_table_name() );
	$data->set( 'link_table', $content_relations_schema->get_table_name() ); // Backwards compatibility

	if ( Mlp_Self_Check::INSTALLATION_CONTEXT_OK === $requirements_check ) {
		$current_version = new Mlp_Semantic_Version_Number( $data->get( 'version' ) );

		$last_version = new Mlp_Semantic_Version_Number( get_site_option( 'mlp_version' ) );

		$updater = new Mlp_Update_Plugin_Data( $data, $wpdb, $current_version, $last_version );

		switch ( $self_check->is_current_version( $current_version, $last_version ) ) {
			case Mlp_Self_Check::NEEDS_INSTALLATION:
				$updater->install_plugin();
				break;

			case Mlp_Self_Check::NEEDS_UPGRADE:
				$deactivator = new Mlp_Network_Plugin_Deactivation();
				$updater->update( $deactivator );
				break;
		}
	}

	return TRUE;
}

register_activation_hook(
	defined( 'MLP_PLUGIN_FILE' ) ? MLP_PLUGIN_FILE : __FILE__,
	'mlp_activation'
);

/**
 * Gets called on plugin activation.
 *
 * @return void
 */
function mlp_activation() {

	if ( ! class_exists( 'Mlp_Activator' ) ) {
		require plugin_dir_path( __FILE__ ) . 'inc/activation/Mlp_Activator.php';
	}

	$activator = new Mlp_Activator();
	$activator->set_transient();
}

/**
 * Write debug data to the error log.
 *
 * Add the following linge to your `wp-config.php` to enable this function:
 *
 *     const MULTILINGUALPRESS_DEBUG = TRUE;
 *
 * @param string $message
 *
 * @return void
 */
function mlp_debug( $message ) {

	if ( ! defined( 'MULTILINGUALPRESS_DEBUG' ) || ! MULTILINGUALPRESS_DEBUG ) {
		return;
	}

	$date = date( 'H:m:s' );

	error_log( "MultilingualPress: $date $message" );
}

if ( defined( 'MULTILINGUALPRESS_DEBUG' ) && MULTILINGUALPRESS_DEBUG ) {
	add_action( 'mlp_debug', 'mlp_debug' );
}
