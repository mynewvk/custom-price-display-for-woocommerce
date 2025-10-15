<?php namespace CustomPriceDisplay\Products\Variable\Features;

use CustomPriceDisplay\Core\ServiceContainerTrait;
use CustomPriceDisplay\CustomPriceDisplayPlugin;
use CustomPriceDisplay\Products\Variable\VariableProductManager;

class HideVariationPrice {
	
	use ServiceContainerTrait;
	
	/**
	 * @var VariableProductManager
	 */
	protected $productManager;
	
	public function __construct( VariableProductManager $productManager ) {
		$this->productManager = $productManager;
		
		add_filter( 'wp_print_styles', array( $this, 'hideIndividualPrices' ), 9999, 2 );
	}
	
	public function hideIndividualPrices() {
		
		$productId = get_queried_object_id();
		
		$product = wc_get_product( $productId );
		
		if ( ! ( $product instanceof \WC_Product_Variable ) ) {
			return;
		}
		
		if ( is_cart() || is_checkout() ) {
			return;
		}
		
		$config = $this->productManager->getConfig( $productId );

		if ( ! $config['hide_variation_price'] ) {
			return;
		}
		
		// Enqueue CSS to hide individual variation prices
		wp_enqueue_style( 'custom-price-display__hide-individual-variation-price',
			$this->getContainer()->getFileManager()->locateAsset( 'frontend/hide-individual-variation-price.css' ),
			array(), CustomPriceDisplayPlugin::VERSION );
	}
}

