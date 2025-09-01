<?php namespace CustomPriceDisplay\Features\IndividualProductConfig;

use CustomPriceDisplay\Core\ServiceContainerTrait;
use CustomPriceDisplay\Features\Feature;

class IndividualProductConfig extends Feature {
	
	use ServiceContainerTrait;
	
	public function getName(): string {
		return 'Individual Product Configuration';
	}
	
	public function getDescription(): string {
		return 'Provides price format settings for individual products.';
	}
	
	public function getSlug(): string {
		return 'individual-product';
	}
	
	public function run() {
		new ProductTab();
		
		add_filter( 'custom_price_display/price_config', array( $this, 'adjustProductPriceConfig' ), 10, 2 );
	}
	
	public function adjustProductPriceConfig( $config, $productId ) {
		$productPriceData = get_post_meta( $productId, '_cpd_custom_price_data', true );
		
		if ( ! is_array( $productPriceData ) ) {
			return $config;
		}
		
		$priceFormat = isset( $productPriceData['format'] ) ? sanitize_text_field( $productPriceData['format'] ) : '';
		
		if ( ! in_array( $priceFormat, array( 'default', 'lowest', 'highest', 'custom' ) ) ) {
			return $config;
		}
		
		$config->setProperty( 'price_display_format', $priceFormat );
		$config->setProperty( 'custom_price_template',
			isset( $productPriceData['template'] ) ? wp_kses_post( $productPriceData['template'] ) : '' );
		$config->setProperty( 'price_suffix_prefix', array(
			'prefix' => isset( $productPriceData['prefix'] ) ? sanitize_text_field( $productPriceData['prefix'] ) : '',
			'suffix' => isset( $productPriceData['suffix'] ) ? sanitize_text_field( $productPriceData['suffix'] ) : '',
		) );

		return $config;
	}
}