<?php namespace CustomPriceDisplay\Products\Variable\Features;

use CustomPriceDisplay\Products\Variable\VariableProductManager;
use CustomPriceDisplay\Core\ServiceContainerTrait;
use CustomPriceDisplay\CustomPriceDisplayPlugin;
use WC_Product_Variable;
use WC_Product;


class UpdateVariablePriceWhenVariationSelected {
	
	use ServiceContainerTrait;
	
	protected $enabled = false;
	
	protected $productManager;
	
	public function __construct( VariableProductManager $productManager ) {
		
		$this->productManager = $productManager;
		
		add_filter( 'woocommerce_get_price_html', array( $this, 'wrapPrice' ), 99999, 2 );
		
		add_action( 'wp_footer', function () {
			
			if ( ! $this->enabled ) {
				return;
			}
			
			wp_enqueue_script( 'custom-price-display__variable-product-price',
				$this->getContainer()->getFileManager()->locateJSAsset( 'frontend/variable-product-price' ),
				array( 'jquery' ), CustomPriceDisplayPlugin::VERSION, true );
			
		}, - 999 );
	}
	
	public function wrapPrice( ?string $defaultPriceHTML, ?WC_Product $product ): ?string {
		
		if ( ! $product ) {
			return $defaultPriceHTML;
		}
		
		if ( is_cart() ) {
			return $defaultPriceHTML;
		}
		
		if ( ! ( $product instanceof WC_Product_Variable ) ) {
			return $defaultPriceHTML;
		}
		
		$config = $this->productManager->getConfig( $product->get_id() );
		
		if ( ! $config['update_variable_price_when_variation_selected'] ) {
			return $defaultPriceHTML;
		}
		
		if ( ! $this->enabled ) {
			$this->enabled = true;
		}
		
		return apply_filters( 'custom_price_display/products/variable/wrapped_price',
			'<span class="cpdfw-variable-product-price" data-product-id="' . $product->get_id() . '">' . $defaultPriceHTML . '</span>',
			$product, $defaultPriceHTML, $this );
	}
}
