<?php namespace CustomPriceDisplay\Products\Variable;

use CustomPriceDisplay\Products\AbstractProductManager;
use CustomPriceDisplay\Products\Variable\Settings\VariableProductsSettings;
use WC_Product;
use WC_Product_Variable;

class VariableProductManager extends AbstractProductManager {
	
	protected $settings;
	
	protected $productTabOptions;
	
	protected $features = array();
	
	public function getSupportedProductTypes(): array {
		return apply_filters( 'custom_price_display/products/variable/supported_variable_types',
			array( 'variable', 'variable-subscription' ) );
	}
	
	public function getPriceHTML( WC_Product $product ): ?string {
		
		// Some themes use ->get_price_html() to show cart item price. Do not modify product price if we're in the cart
		if ( is_cart() ) {
			return null;
		}
		
		$config = $this->getConfig( $product->get_id() );
		
		if ( ! ( $product instanceof WC_Product_Variable ) ) {
			return null;
		}
		
		if ( ! $this->isEnabledForProduct( $config, $product->get_id() ) ) {
			return null;
		}
		
		if ( 'default' === $config['price_display_format'] ) {
			return null;
		}
		
		if ( 'lowest' === $config['price_display_format'] ) {
			$basePrice = $product->get_variation_price( 'min', true );
		} elseif ( 'highest' === $config['price_display_format'] ) {
			$basePrice = $product->get_variation_price( 'max', true );
		} elseif ( 'custom' === $config['price_display_format'] ) {
			
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
			
			$priceHTML = wc_price( $basePrice );
			
			$priceHTML = $prefix . ' ' . $priceHTML . ' ' . $suffix . ' ' . $product->get_price_suffix();
		}
		
		return apply_filters( 'custom_price_display/products/variable/price_html', $priceHTML, $product, $config );
	}
	
	public function init(): void {
		$this->settings          = new VariableProductsSettings( $this );
		$this->productTabOptions = new VariableProductTabOptions( $this );
		
		$this->features = array(
			new Features\UpdateVariablePriceWhenVariationSelected( $this ),
			new Features\HideVariationPrice( $this ),
		);
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
			
			'update_variable_price_when_variation_selected' => false,
			'hide_variation_price'                          => false,
		);
		
		/**
		 * @hooked Settings::loadSettings - 10
		 * @hooked ProductTabOptions::loadSettings - 20
		 */
		return apply_filters( 'custom_price_display/products/variable/config', $defaultConfig, $productId );
	}
}