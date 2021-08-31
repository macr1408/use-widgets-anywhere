<?php
/**
 * Plugin main file
 *
 * @package widgets-anywhere/main-file
 */

/**
 * Plugin Name: Use Widgets Anywhere!
 * Description: Creates a Widget shortcode so you can output any widget anywhere! just use the shortcode [widget_anywhere]
 * Version: 1.0.0
 * Requires PHP: 7.1
 * Author: CRPlugins
 * Author URI: https://crplugins.com.ar
 * Text Domain: widgets-anywhere
 * Domain Path: /i18n/languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', array( 'Widgets_Anywhere', 'plugins_loaded' ) );

/**
 * Plugin's base Class
 */
class Widgets_Anywhere {

	const MAIN_FILE = __FILE__;
	const MAIN_DIR  = __DIR__;

	/**
	 * Plugin's logic when the plugins are loaded
	 *
	 * @return void
	 */
	public static function plugins_loaded(): void {
		if ( ! self::check_system( true ) ) {
			return;
		}

		self::load_files();
		self::load_textdomain();

		add_action( 'init', array( __CLASS__, 'init' ) );
		add_action( 'admin_notices', array( 'CRPlugins\WidgetsAnywhere\Helper', 'check_notices' ) );
	}

	/**
	 * Plugin's logic when wp inits
	 *
	 * @return void
	 */
	public static function init(): void {
		add_shortcode( 'widget_anywhere', array( __CLASS__, 'widget_anywhere_shortcode' ) );
	}

	/**
	 * Handles the [widget_anywhere] functionality
	 *
	 * @param array|string $args shortcode args.
	 * @return string shortcode output.
	 */
	public static function widget_anywhere_shortcode( $args ): string {
		if ( empty( $args['widget_class'] ) ) {
			return '';
		}

		// Widget instance's settings. Use wp_parse_args instead of shortcode_atts to accept custom instance settings.
		$instance_settings = array(
			'title' => '',
		);
		$instance_settings = wp_parse_args( $args, $instance_settings );

		if ( ! class_exists( $args['widget_class'] ) ) {
			return '';
		}

		try {
			$widget                = new $args['widget_class']();
			$before_widget_classes = 'widget';
			if ( ! empty( $args['parent_class'] ) ) {
				$before_widget_classes .= ' ' . $args['parent_class'];
			}
			if ( property_exists( $widget, 'widget_cssclass' ) ) {
				$before_widget_classes .= ' ' . $widget->widget_cssclass;
			}
			$widget_args = array(
				'before_title'   => '',
				'after_title'    => '',
				'before_sidebar' => '',
				'after_sidebar'  => '',
				'before_widget'  => '<div class="' . esc_attr( $before_widget_classes ) . '">',
				'after_widget'   => '</div>',
				'widget_name'    => '',
				'widget_id'      => '',
			);

			ob_start();
			$widget->widget( $widget_args, $instance_settings );
			$output = ob_get_clean();
			return $output;
		} catch ( \Throwable $error ) {
			ob_get_clean();
			return '';
		}

	}

	/**
	 * Loads all the files included in this plugin
	 *
	 * @return void
	 */
	public static function load_files(): void {
		require_once __DIR__ . '/includes/helper/trait-notices.php';
		require_once __DIR__ . '/includes/helper/class-helper.php';
	}

	/**
	 * Checks system requirements
	 *
	 * @param boolean $show_notice whether if it should show a notice to the admin screen or not.
	 * @return boolean
	 */
	public static function check_system( bool $show_notice ): bool {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$system = self::check_components();

		if ( $system['flag'] ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			if ( $show_notice ) {
				echo '<div class="notice notice-error is-dismissible">';
				/* translators: %1$s is replaced with the outdated requirement and %2$s with its version */
				echo '<p>' . sprintf( esc_html__( '<strong>Widgets Anywhere!</strong> Requires at least %1$s version %2$s or greater.', 'widgets-anywhere' ), esc_html( $system['flag'] ), esc_html( $system['version'] ) ) . '</p>';
				echo '</div>';
			}
			return false;
		}

		return true;
	}

	/**
	 * Checks the components required for the plugin to work (PHP and WordPress)
	 *
	 * @return array
	 */
	private static function check_components(): array {

		global $wp_version;
		$flag    = false;
		$version = false;

		if ( version_compare( PHP_VERSION, '7.1', '<' ) ) {
			$flag    = 'PHP';
			$version = '7.1';
		} elseif ( version_compare( $wp_version, '5.0', '<' ) ) {
			$flag    = 'WordPress';
			$version = '5.0';
		}

		return array(
			'flag'    => $flag,
			'version' => $version,
		);
	}

	/**
	 * Loads the plugin text domain
	 *
	 * @return void
	 */
	public static function load_textdomain(): void {
		load_plugin_textdomain( 'widgets-anywhere', false, basename( dirname( __FILE__ ) ) . '/i18n/languages' );
	}
}

