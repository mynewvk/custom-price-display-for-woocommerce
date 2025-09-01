<?php namespace CustomPriceDisplay;

use Freemius;

/**
 * Class License
 *
 * @package CustomPriceDisplay
 */
class License {
	
	/**
	 * License
	 *
	 * @var Freemius
	 */
	private $instance;
	
	public function __construct() {
		
		$this->init();
		
		if ( $this->isValid() ) {
			$this->hooks();
		}
	}
	
	public function hooks() {
		add_action( 'admin_menu', [ $this, 'initPages' ] );
		
		$this->instance->add_filter( 'pricing/show_annual_in_monthly', '__return_false' );
	}
	
	public function isValid(): bool {
		return $this->instance instanceof Freemius;
	}
	
	public function init() {
		if ( function_exists( 'cpd_fs' ) ) {
			$this->instance = cpd_fs();
		}
	}
	
	public function initPages() {
		
		// Account
		add_submenu_page( '__freemius', __( 'Freemius Account', 'custom-price-display-for-woocommerce' ),
			__( 'Freemius Account', 'custom-price-display-for-woocommerce' ), 'manage_options', 'custom-price-display-account',
			[ $this, 'renderAccountPage' ] );
		
		// Contact us
		add_submenu_page( '__freemius', __( 'Contact Us', 'custom-price-display-for-woocommerce' ),
			__( 'Contact Us', 'custom-price-display-for-woocommerce' ), 'manage_options', 'custom-price-display-contact-us',
			[ $this, 'renderContactUsPage' ] );
	}
	
	public function renderAccountPage() {
		
		if ( ! $this->instance->get_user() || $this->instance->is_activation_mode() || $this->instance->is_anonymous() ) {
			wp_safe_redirect( admin_url( 'admin.php?page=custom-price-display-for-woocommerce' ) );
			exit;
		} else {
			$this->instance->_account_page_load();
			$this->instance->_account_page_render();
		}
	}
	
	public function renderContactUsPage() {
		$this->instance->_contact_page_render();
	}
}