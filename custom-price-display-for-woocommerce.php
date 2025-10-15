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
	 * WC tested up to: 10.2
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
	
	if ( ! function_exists( 'cpdfw_initFreemius' ) ) {
		function cpdfw_initFreemius() {
			
			function cpdfw_fs() {
				global $cpdfw_fs;
				
				if ( ! isset( $cpdfw_fs ) ) {
					// Include Freemius SDK.
					require_once dirname( __FILE__ ) . '/vendor/freemius/wordpress-sdk/start.php';
					
					if ( ! defined( 'WP_FS__PRODUCT_20371_MULTISITE' ) ) {
						define( 'WP_FS__PRODUCT_20371_MULTISITE', true );
					}
					
					$cpdfw_fs = fs_dynamic_init( array(
						'id'                  => '20371',
						'slug'                => 'custom-price-display-for-woocommerce',
						'type'                => 'plugin',
						'public_key'          => 'pk_bac60cb8b1e125d7499bc67826e70',
						'is_premium'          => true,
						'premium_suffix'      => 'Premium',
						// If your plugin is a serviceware, set this option to false.
						'has_premium_version' => true,
						'has_addons'          => false,
						'has_paid_plans'      => true,
						'trial'               => array(
							'days'               => 7,
							'is_require_payment' => true,
						),
						'wp_org_gatekeeper'   => 'OA7#BoRiBNqdf52FvzEf!!074aRLPs8fspif$7K1#4u4Csys1fQlCecVcUTOs2mcpeVHi#C2j9d09fOTvbC0HloPT7fFee5WdS3G',
						'menu'                => array(
							'first-path' => 'admin.php?page=wc-settings&tab=products&section=custom_price_display_settings',
							'contact'    => false,
							'support'    => false,
						),
						'is_org_compliant'    => true,
					) );
				}
				
				return $cpdfw_fs;
			}
			
			// Init Freemius.
			cpdfw_fs();
			
			// Signal that SDK was initiated.
			do_action( 'cpdfw_fs_loaded' );
		}
	}
	if ( ! function_exists( 'cpdfw_fs_activation_url' ) ) {
		function cpdfw_fs_activation_url(): ?string {
			return cpdfw_fs()->is_activation_mode() ? cpdfw_fs()->get_activation_url() : cpdfw_fs()->get_upgrade_url();
		}
	}
	
	if ( function_exists( 'cpdfw_fs' ) ) {
		cpdfw_fs()->set_basename( true, __FILE__ );
		
		return;
	} else {
		
		cpdfw_initFreemius();
		
		call_user_func( function () {
			
			require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
			
			$plugin = new CustomPriceDisplayPlugin( __FILE__ );
			
			if ( $plugin->checkRequirements() ) {
				
				register_activation_hook( __FILE__, array( $plugin, 'activate' ) );
				
				$plugin->run();
			}
		} );
	}