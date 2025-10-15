<?php namespace CustomPriceDisplay;

use CustomPriceDisplay\ViewComponents\WPEditor;
use CustomPriceDisplay\Core\AdminNotifier;
use CustomPriceDisplay\Core\FileManager;
use CustomPriceDisplay\Core\ServiceContainerTrait;
use CustomPriceDisplay\Products\ProductService;
use CustomPriceDisplay\Settings\Settings;
use Automattic\WooCommerce\Utilities\FeaturesUtil;

class CustomPriceDisplayPlugin {
	
	const VERSION = '1.0.0';
	
	use ServiceContainerTrait;
	
	protected $mainFile;
	
	public function __construct( string $mainFile ) {
		
		$this->mainFile = $mainFile;
		
		$this->bootCoreServices();
		$this->declareCompatibilities();
	}
	
	protected function bootCoreServices() {
		$this->getContainer()->add( 'fileManager', new FileManager( $this->mainFile ) );
		$this->getContainer()->add( 'adminNotifier', new AdminNotifier() );
	}
	
	protected function declareCompatibilities() {
		add_action( 'before_woocommerce_init', function () {
			if ( class_exists( FeaturesUtil::class ) ) {
				FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->mainFile );
				FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $this->mainFile );
				FeaturesUtil::declare_compatibility( 'product_block_editor', $this->mainFile );
			}
		} );
	}
	
	public function initializationHooks() {
		add_filter( 'plugin_row_meta', array( $this, 'addPluginsMeta' ), 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( $this->getContainer()->getFileManager()->getMainFile() ),
			array( $this, 'addPluginActions' ), 10, 4 );
	}
	
	public function checkRequirements(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		
		// Check if WooCommerce is active
		if ( ! ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) ) {
			/* translators: %s: required plugin */
			$message = sprintf( __( '<b>Custom Price Display</b> plugin requires %s to be installed and activated.',
				'custom-price-display-for-woocommerce' ),
				'<a target="_blank" href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a>' );
			
			$this->getContainer()->getAdminNotifier()->push( $message, AdminNotifier::ERROR );
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Entry point when every requirement is passed
	 */
	public function run() {
		
		$this->getContainer()->add( 'settings', new Settings() );
		
		$this->initializationHooks();
		
		// Init Services
		add_action( 'init', function () {
			$this->getContainer()->initService( ProductService::class );
			
			WPEditor::instance();
		} );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );
	}
	
	public function enqueueScripts( $screen ) {
		
		wp_enqueue_style( 'custom-price-display__main-style',
			$this->getContainer()->getFileManager()->locateAsset( 'admin/main.css' ), array(),
			CustomPriceDisplayPlugin::VERSION );
		
		wp_enqueue_script( 'custom-price-display__main-script',
			$this->getContainer()->getFileManager()->locateJSAsset( 'admin/main' ), array(),
			CustomPriceDisplayPlugin::VERSION, true );
	}
	
	/**
	 * Add setting to plugin actions at plugins' list
	 *
	 * @param  array  $actions
	 *
	 * @return array
	 */
	public function addPluginActions( array $actions ): array {
		$actions[] = '<a href="' . $this->getContainer()->getSettings()->getLink() . '">' . __( 'Settings',
				'custom-price-display-for-woocommerce' ) . '</a>';
		
		return $actions;
	}
	
	public function addPluginsMeta( $links, $file ) {
		
		if ( strpos( $file, 'custom-price-display-for-woocommerce' ) === false ) {
			return $links;
		}
		
		$links['docs'] = '<a target="_blank" href="' . self::getDocumentationURL() . '">' . __( 'Documentation',
				'custom-price-display-for-woocommerce' ) . '</a>';
		
		return $links;
	}
	
	/**
	 * Fired after plugin's uninstall
	 */
	public static function uninstall() {
		delete_option( 'cpdfw_plugin_activation_timestamp' );
	}
	
	/**
	 * Plugin activation
	 */
	public function activate() {
		$this->saveActivationTime();
	}
	
	public static function getDocumentationURL(): string {
		return '';
	}
	
	public function saveActivationTime() {
		if ( ! get_option( 'cpdfw_plugin_activation_timestamp', false ) ) {
			update_option( 'cpdfw_plugin_activation_timestamp', time() );
		}
	}
}