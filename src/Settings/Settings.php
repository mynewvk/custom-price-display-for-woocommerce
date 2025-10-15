<?php namespace CustomPriceDisplay\Settings;

use CustomPriceDisplay\Core\ServiceContainerTrait;
use CustomPriceDisplay\CustomPriceDisplayPlugin;
use CustomPriceDisplay\Settings\CustomOptions\Checkbox;
use CustomPriceDisplay\Settings\CustomOptions\MultipleTextInputs;
use CustomPriceDisplay\Settings\CustomOptions\RichText;
use CustomPriceDisplay\Settings\CustomOptions\SelectedProducts;
use CustomPriceDisplay\Settings\CustomOptions\SwitchOption;
use CustomPriceDisplay\Settings\Sections\Section;
use CustomPriceDisplay\Settings\Sections\VariableProducts;

/**
 * Class Settings
 *
 * @package CustomPriceDisplay\Settings
 */
class Settings {
	
	use ServiceContainerTrait;
	
	const SETTINGS_PREFIX = 'custom_price_display_';
	
	const SETTINGS_SECTION = 'custom_price_display_settings';
	
	/**
	 * Settings
	 *
	 * @var array
	 */
	private $settings = array();
	
	/**
	 * Settings sections
	 *
	 * @var array
	 */
	protected $sections = array();
	
	/**
	 * Conditionals for settings
	 *
	 * @var array
	 */
	protected $conditionals = array();
	
	/**
	 * Settings constructor.
	 */
	public function __construct() {
		$this->initCustomOptions();
		$this->hooks();
		
		new LookupService();
	}
	
	protected function initCustomOptions() {
		$this->getContainer()->add( 'settings.custom_options.checkbox', new Checkbox() );
		$this->getContainer()->add( 'settings.custom_options.switch-option', new SwitchOption() );
		$this->getContainer()->add( 'settings.custom_options.multiple-text-inputs', new MultipleTextInputs() );
		$this->getContainer()->add( 'settings.custom_options.rich-text', new RichText() );
		$this->getContainer()->add( 'settings.custom_options.selected-products', new SelectedProducts() );
	}
	
	/**
	 * Register hooks
	 */
	public function hooks() {
		
		add_action( 'init', array( $this, 'initSections' ), 999999 );
		
		add_filter( 'woocommerce_get_sections_products', array( $this, 'addSettingsSection' ), 50 );
		add_filter( 'woocommerce_get_settings_products', function ( $settings, $current_section ) {
			if ( self::SETTINGS_SECTION === $current_section ) {
				return $this->getSettings();
			}
			
			return $settings;
		}, 10, 2 );
		
		add_action( 'admin_enqueue_scripts', function ( $screen ) {
			// Only load on settings page
			if ( 'woocommerce_page_wc-settings' === $screen ) {
				wp_register_script( 'custom-price-display__settings-script',
					$this->getContainer()->getFileManager()->locateJSAsset( 'admin/settings' ), array(),
					CustomPriceDisplayPlugin::VERSION, true );
			}
		} );
		
		add_action( 'admin_footer', function () {
			
			wp_localize_script( 'custom-price-display__settings-script', 'customPriceDisplaySettingsConditionals',
				$this->conditionals );
			
			wp_enqueue_script( 'custom-price-display__settings-script' );
			
		}, - 999 );
	}
	
	public function initSections() {
		$this->sections = apply_filters( 'custom_price_display/settings/sections', array() );
		
		$this->sections = array_filter( $this->sections, function ( $section ) {
			return class_exists( $section ) && is_subclass_of( $section, Section::class );
		} );
		
		$this->sections = array_map( function ( $section ) {
			return new $section();
		}, $this->sections );
	}
	
	public function getSection( $name ) {
		
		foreach ( $this->sections as $section ) {
			if ( $section instanceof $name ) {
				return $section;
			}
		}
		
		return null;
	}
	
	public function getSettings(): array {
		
		$settings = array();
		
		foreach ( $this->sections as $section ) {
			
			$settings[] = array(
				'title' => $section->getName(),
				'type'  => 'title',
				'desc'  => $section->getDescription(),
				'id'    => self::SETTINGS_SECTION . '_' . $section->getSlug(),
			);
			
			$settings = array_merge( $settings, $section->getSettings() );
			
			$settings[] = array(
				'type' => 'sectionend',
				'id'   => self::SETTINGS_SECTION . '_' . $section->getSlug(),
			);
		}
		
		$this->collectConditions( $settings );
		
		return $settings;
	}
	
	public function collectConditions( array $settings ) {
		
		foreach ( $settings as $setting ) {
			
			$conditionals = isset( $setting['conditionals'] ) ? (array) $setting['conditionals'] : array();
			
			$_conditionals = array();
			
			foreach ( $conditionals as $conditional ) {
				$_conditionals[ $conditional['id'] ] = $conditional['value'] ?? array();
			}
			
			$this->conditionals[ $setting['id'] ] = $_conditionals;
		}
	}
	
	public function addSettingsSection( $tabs ): array {
		
		$newTabs = array();
		
		foreach ( $tabs as $key => $tab ) {
			$newTabs[ $key ] = $tab;
			
			if ( "" === $key ) {
				$newTabs[ self::SETTINGS_SECTION ] = __( 'Custom price display',
					'custom-price-display-for-woocommerce' );
			}
		}
		
		return $newTabs;
	}
	
	/**
	 * Get setting by name
	 *
	 * @param  string  $optionName
	 * @param  mixed  $default
	 *
	 * @return mixed
	 */
	public function get( string $optionName, $default = null ) {
		return get_option( self::SETTINGS_PREFIX . $optionName, $default );
	}
	
	/**
	 * Get url to settings page
	 *
	 * @return string
	 */
	public function getLink(): string {
		return admin_url( 'admin.php?page=wc-settings&tab=products&section=' . self::SETTINGS_SECTION );
	}
}