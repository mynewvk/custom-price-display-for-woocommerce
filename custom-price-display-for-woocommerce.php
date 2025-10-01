<?php
	/**
	 * Plugin Name:       Custom Price Display for WooCommerce
	 * Description:       Show the lowest price of a variable product, add custom price labels.
	 * Version:           1.0.0
	 * Author:            U2Code
	 * Author URI:        https://u2code.com
	 * License:           GNU General Public License v3.0
	 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
	 * Text Domain:       custom-price-display-for-woocommerce
	 * Domain Path:       /languages/
	 *
	 * WC requires at least: 7.0
	 * WC tested up to: 10.1
	 *
	 * Requires at least: 5.0
	 * Requires PHP: 7.2
	 * Tested up to: 6.8
	 *
	 */

	use CustomPriceDisplay\CustomPriceDisplayPlugin;

	// If this file is called directly, abort.
	if ( ! defined( 'WPINC' ) ) {
		die;
	}

	if ( version_compare( phpversion(), '7.2.0', '<' ) ) {

		add_action( 'admin_notices', function () {
			?>
			<div class='notice notice-error'>
				<p>
					Custom Price Display plugin requires PHP version to be <b>7.2 or higher</b>. You run PHP
					version <?php echo esc_attr( phpversion() ); ?>
				</p>
			</div>
			<?php
		} );

		return;
	}

	call_user_func( function () {

		require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

		$plugin = new CustomPriceDisplayPlugin( __FILE__ );

		if ( $plugin->checkRequirements() ) {

			register_activation_hook( __FILE__, array( $plugin, 'activate' ) );

			$plugin->run();
		}
	} );
