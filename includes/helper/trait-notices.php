<?php
/**
 * Trait containing functions for notices
 *
 * @package widgets-anywhere/trait-notices
 */

namespace CRPlugins\WidgetsAnywhere\Helper;

/**
 * AssetsTrait class defining functions for assets
 */
trait Notices_Trait {

	/**
	 * Checks if there are notices to print and prints them
	 *
	 * @return void
	 */
	public static function check_notices(): void {
		$notices_types = array( 'error', 'success', 'info' );
		foreach ( $notices_types as $type ) {
			$notices = get_transient( 'widgets-anywhere-' . $type . '-notices' );
			if ( empty( $notices ) ) {
				continue;
			}
			foreach ( $notices as $notice ) {
				echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible">';
				echo '<p>' . esc_html( $notice ) . '</p>';
				echo '</div>';
			}
			delete_transient( 'widgets-anywhere-' . $type . '-notices' );
		}
	}

	/**
	 * Creates a generic notice
	 *
	 * @param string  $type severity of notice.
	 * @param string  $msg text of the notice.
	 * @param boolean $do_action whether the notice should be triggered right away or not.
	 * @return void
	 */
	private static function add_notice( string $type, string $msg, bool $do_action = false ): void {
		$notices = get_transient( 'widgets-anywhere-' . $type . '-notices' );
		if ( ! empty( $notices ) ) {
			$notices[] = $msg;
		} else {
			$notices = array( $msg );
		}
		set_transient( 'widgets-anywhere-' . $type . '-notices', $notices, 60 );
		if ( $do_action ) {
			do_action( 'admin_notices' );
		}
	}

	/**
	 * Adds an error notice
	 *
	 * @param string  $msg text of the notice.
	 * @param boolean $do_action whether the notice should be triggered right away or not.
	 * @return void
	 */
	public static function add_error( string $msg, bool $do_action = false ): void {
		self::add_notice( 'error', $msg, $do_action );
	}

	/**
	 * Adds a success notice
	 *
	 * @param string  $msg text of the notice.
	 * @param boolean $do_action whether the notice should be triggered right away or not.
	 * @return void
	 */
	public static function add_success( string $msg, bool $do_action = false ): void {
		self::add_notice( 'success', $msg, $do_action );
	}

	/**
	 * Adds an info notice
	 *
	 * @param string  $msg text of the notice.
	 * @param boolean $do_action whether the notice should be triggered right away or not.
	 * @return void
	 */
	public static function add_info( string $msg, bool $do_action = false ): void {
		self::add_notice( 'info', $msg, $do_action );
	}
}
