<?php namespace CustomPriceDisplay\Products\Simple;

use CustomPriceDisplay\Products\AbstractProductManager;
use CustomPriceDisplay\Products\Simple\Settings\SimpleProductSettings;
use WC_Product;

class SimpleProductManager extends AbstractProductManager {
	
	protected $settings;
	
	protected $productTabOptions;
	
	protected $features = array();
	
	public function getSupportedProductTypes(): array {
		return apply_filters( 'custom_price_display/products/simple/supported_simple_types',
			array( 'simple', 'subscription' ) );
	}
	
	public function getPriceHTML( WC_Product $product ): ?string {
		
		// Some themes use ->get_price_html() to show cart item price. Do not modify product price if we're in the cart
		if ( is_cart() ) {
			return null;
		}
		
		$config = $this->getConfig( $product->get_id() );
		
		if ( ! $product->is_type( $this->getSupportedProductTypes() ) ) {
			return null;
		}
		
		if ( ! $this->isEnabledForProduct( $config, $product->get_id() ) ) {
			return null;
		}
		
		if ( 'default' === $config['price_display_format'] ) {
			return null;
		}
		
		if ( 'custom' === $config['price_display_format'] ) {
			
			$priceTemplate = $config['custom_price_template'];
			
			$priceHTML = str_replace( [ '{cpdfw_lowest_price}', '{cpdfw_highest_price}' ], [
				wc_price( $product->get_variation_price( 'min', true ) ),
				wc_price( $product->get_variation_price( 'max', true ) ),
			], $priceTemplate );
			
		} else {
			return null;
		}
		
		if ( ! isset( $priceHTML ) ) {
			$priceSuffixPrefix = $config['price_suffix_prefix'];
			$suffix            = isset( $priceSuffixPrefix['suffix'] ) ? $priceSuffixPrefix['suffix'] : '';
			$prefix            = isset( $priceSuffixPrefix['prefix'] ) ? $priceSuffixPrefix['prefix'] : '';
			
			$priceHTML = wc_price( $product->get_price() );
			
			$priceHTML = $prefix . ' ' . $priceHTML . ' ' . $suffix . ' ' . $product->get_price_suffix();
		}
		
		return apply_filters( 'custom_price_display/products/simple/price_html', $priceHTML, $product, $config );
	}
	
	public function init(): void {
		$this->settings          = new SimpleProductSettings( $this );
		$this->productTabOptions = new SimpleProductTabOptions( $this );
		
		$this->features = array();
	}
	
	public function getConfig( $productId ): array {
		$defaultConfig = array(
			'price_display_format' => 'lowest',
			
			'price_suffix_prefix' => array(
				'prefix' => '',
				'suffix' => '',
			),
			
			'custom_price_template' => '',
			'enable_for'            => 'all',
			
			'selected_products_categories' => array(
				'included_products'   => array(),
				'excluded_products'   => array(),
				'included_categories' => array(),
				'excluded_categories' => array(),
			),
		);
		
		/**
		 * @hooked Settings::loadSettings - 10
		 * @hooked ProductTabOptions::loadSettings - 20
		 */
		return apply_filters( 'custom_price_display/products/simple/config', $defaultConfig, $productId );
	}
}