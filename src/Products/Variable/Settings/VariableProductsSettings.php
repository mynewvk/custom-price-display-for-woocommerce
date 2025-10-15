<?php namespace CustomPriceDisplay\Products\Variable\Settings;

use CustomPriceDisplay\Core\ServiceContainerTrait;
use CustomPriceDisplay\Settings\Sections\Section;

class VariableProductsSettings {
	
	use ServiceContainerTrait;
	
	/**
	 * @var Section
	 */
	protected $settingsSection;
	
	public function __construct() {
		add_filter( 'custom_price_display/settings/sections', function ( $sections ) {
			
			$sections[] = VariableProductSettingsSection::class;
			
			return $sections;
		} );
		
		add_action( 'custom_price_display/products/variable/config', array( $this, 'loadSettings' ), 10 );
	}
	
	public function getSettingsSection(): Section {
		
		if ( is_null( $this->settingsSection ) ) {
			$this->settingsSection = $this->getContainer()->getSettings()->getSection( VariableProductSettingsSection::class );
		}
		
		return $this->settingsSection;
		
	}
	
	public function getOption( $name, $default = null ) {
		return $this->getSettingsSection()->getOptionValue( $name, $default );
	}
	
	public function loadSettings( array $config ): array {
		
		$config['price_display_format'] = $this->getOption( 'price_display_format', $config['price_display_format'] );
		$config['price_suffix_prefix']  = $this->getOption( 'price_suffix_prefix', $config['price_suffix_prefix'] );
		
		$config['custom_price_template'] = $this->getOption( 'custom_price_template',
			$config['custom_price_template'] );
		
		$config['enable_for'] = $this->getOption( 'enable_for', $config['enable_for'] );
		
		$config['selected_products_categories'] = $this->getOption( 'selected_products_categories',
			$config['selected_products_categories'] );
		
		$config['update_variable_price_when_variation_selected'] = $this->getOption( 'update_variable_price_when_variation_selected',
				wc_bool_to_string( $config['update_variable_price_when_variation_selected'] ) ) === 'yes';
		
		$config['hide_variation_price'] = $this->getOption( 'hide_variation_price',
				wc_bool_to_string( $config['hide_variation_price'] ) ) === 'yes';
		
		return $config;
	}
}
