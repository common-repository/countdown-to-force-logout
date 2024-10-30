<?php
/**
 * Countdown to force logout
 *
 * @package    Countdown to force logout
 * @subpackage CountdownToForceLogout Main Functions
/*
	Copyright (c) 2022- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$countdowntoforcelogout = new CountdownToForceLogout();

/** ==================================================
 * Main Functions
 */
class CountdownToForceLogout {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		add_action( 'rest_api_init', array( $this, 'register_rest' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 10, 1 );
		add_action( 'admin_bar_menu', array( $this, 'customize_admin_bar_menu' ), 9999 );
		add_action( 'admin_print_scripts', array( $this, 'admin_bar_style' ) );
	}

	/** ==================================================
	 * Load script
	 *
	 * @param string $hook_suffix  hook_suffix.
	 * @since 1.00
	 */
	public function admin_scripts( $hook_suffix ) {

		$asset_file = include plugin_dir_path( __DIR__ ) . 'guten/dist/countdowntoforcelogout.asset.php';

		wp_enqueue_style(
			'countdowntoforcelogout-style',
			plugin_dir_url( __DIR__ ) . 'guten/dist/countdowntoforcelogout.css',
			array( 'wp-components' ),
			'1.0.0',
		);

		wp_enqueue_script(
			'countdowntoforcelogout',
			plugin_dir_url( __DIR__ ) . 'guten/dist/countdowntoforcelogout.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		$expiration = 0;
		$period_time = 0;
		$countdown = null;
		$limit_sec = apply_filters( 'countdown_to_force_logout_limit_sec', 600 );
		$limit_modal = false;

		$session = get_user_meta( get_current_user_id(), 'session_tokens' );
		if ( ! empty( $session ) ) {
			$key = array_key_first( $session[0] );
			$expiration = $session[0][ $key ]['expiration'];
			list( $period_time, $countdown ) = $this->period( intval( $expiration ) );
			if ( $limit_sec > $period_time ) {
				$limit_modal = true;
			}
		}

		wp_localize_script(
			'countdowntoforcelogout',
			'countdowntoforcelogout_data',
			array(
				'expiration' => intval( $expiration ),
				'period_time' => intval( $period_time ),
				'countdown' => $countdown,
				'limit_sec' => intval( $limit_sec ),
				'limit_modal' => $limit_modal,
				'modal_title' => __( 'Login validity period', 'countdown-to-force-logout' ),
				'description' => __( 'After the login validity period, you will be forced to logout. In that case, please login again.', 'countdown-to-force-logout' ),
			)
		);
	}

	/** ==================================================
	 * Register Rest API
	 *
	 * @since 1.00
	 */
	public function register_rest() {

		register_rest_route(
			'rf/countdown_to_force_logout_api',
			'/token',
			array(
				'methods' => 'GET',
				'callback' => array( $this, 'api_get' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/** ==================================================
	 * Rest API get
	 *
	 * @since 1.00
	 */
	public function api_get() {

		$expiration = 0;
		$session = get_user_meta( get_current_user_id(), 'session_tokens' );
		if ( ! empty( $session ) ) {
			$key = array_key_first( $session[0] );
			$expiration = $session[0][ $key ]['expiration'];
		}

		list( $response['period_time'], $response['countdown'] ) = $this->period( intval( $expiration ) );

		return new WP_REST_Response( $response, 200 );
	}

	/** ==================================================
	 * Period time & Countdown text
	 *
	 * @param int $expiration  Expiration unix time.
	 * @since 1.00
	 */
	private function period( $expiration ) {

		$period_time = $expiration - time();
		$period_hour_only = floor( $period_time / 3600 );
		$period_day_def = $period_time / 3600 / 24;
		$period_day = floor( $period_day_def );
		$period_hour_def = ( $period_day_def - $period_day ) * 24;
		$period_hour = floor( $period_hour_def );
		$period_min_def = ( $period_hour_def - $period_hour ) * 60;
		$period_min = floor( $period_min_def );
		$period_sec_def = ( $period_min_def - $period_min ) * 60;
		$period_sec = floor( $period_sec_def );

		if ( 86400 <= $period_time ) {
			/* translators: Countdown %1$d: days %2$s: hours %3$s: minutes */
			$countdown = sprintf( __( '%1$d days %2$s hours %3$s minutes', 'countdown-to-force-logout' ), $period_day, sprintf( '%02d', $period_hour ), sprintf( '%02d', $period_min ) );
		} else if ( 86400 > $period_time && 3600 <= $period_time ) {
			/* translators: Countdown %1$s: hours %2$s: minutes */
			$countdown = sprintf( __( '%1$s hours %2$s minutes', 'countdown-to-force-logout' ), sprintf( '%02d', $period_hour ), sprintf( '%02d', $period_min ) );
		} else {
			/* translators: Countdown %1$s: minutes */
			$countdown = sprintf( __( '%1$s minutes', 'countdown-to-force-logout' ), sprintf( '%02d', $period_min ) );
		}

		return array( $period_time, $countdown );
	}

	/** ==================================================
	 * Admin Bar Menu
	 *
	 * @param array $wp_admin_bar  wp_admin_bar.
	 * @since 1.00
	 */
	public function customize_admin_bar_menu( $wp_admin_bar ) {

		$countdown = '<span id="countdowntoforcelogout"></span>';

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'countdowntoforcelogout-bar-menu',
				'title' => __( 'Login validity period', 'countdown-to-force-logout' ) . ' : ' . $countdown,
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'        => 'countdowntoforcelogout-bar-description',
				'parent'    => 'countdowntoforcelogout-bar-menu',
				'title'     => __( 'After the login validity period, you will be forced to logout. In that case, please login again.', 'countdown-to-force-logout' ),
			)
		);
	}

	/** ==================================================
	 * Admin Bar Style
	 *
	 * @since 1.03
	 */
	public function admin_bar_style() {

		?>
		<style>
			@media screen and (max-width: 782px) {
				li#wp-admin-bar-countdowntoforcelogout-bar-menu {
					display: block !important;
				}
			}
		</style>
		<?php
	}
}


