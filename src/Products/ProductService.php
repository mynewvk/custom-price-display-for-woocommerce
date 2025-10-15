<?php namespace CustomPriceDisplay\Products;

use CustomPriceDisplay\Core\ServiceContainerTrait;
use CustomPriceDisplay\CustomPriceDisplayPlugin;
use CustomPriceDisplay\Products\Simple\SimpleProductManager;
use CustomPriceDisplay\Products\Variable\VariableProductManager;
use Exception;
use WC_Product;

class ProductService {
	
	use ServiceContainerTrait;
	
	protected $productManagers;
	
	public function __construct() {
		$this->initProductManagers();
		$this->hooks();
	}
	
	protected function hooks() {
		add_filter( 'woocommerce_get_price_html', array( $this, 'adjustPriceHTML' ), 10, 2 );
		add_action( 'wp_loaded', function () {
			
			$supportedTypes = [];
			
			foreach ( $this->productManagers as $productManager ) {
				try {
					$service        = $this->getContainer()->get( $productManager );
					$supportedTypes = array_merge( $supportedTypes, $service->getSupportedProductTypes() );
				} catch ( Exception $e ) {
					wc_doing_it_wrong( __METHOD__, $e->getMessage(), CustomPriceDisplayPlugin::VERSION );
				}
			}
			
			$args['types'] = array_unique( $supportedTypes );
			
			new ProductTab( $args );
		} );
	}
	
	protected function initProductManagers() {
		$this->productManagers = apply_filters( 'custom_price_display/product_types', array(
			VariableProductManager::class,
			SimpleProductManager::class,
		) );
		
		$this->productManagers = array_filter( $this->productManagers, function ( $productManager ) {
			return class_exists( $productManager ) && is_subclass_of( $productManager, AbstractProductManager::class );
		} );
		
		// Initialize all product managers
		foreach ( $this->productManagers as $productManager ) {
			
			$this->getContainer()->initService( $productManager );
			
			try {
				$service = $this->getContainer()->get( $productManager );
				$service->init();
			} catch ( Exception $e ) {
				wc_doing_it_wrong( __METHOD__, $e->getMessage(), CustomPriceDisplayPlugin::VERSION );
			}
		}
	}
	
	public function adjustPriceHTML( $priceHTML, WC_Product $product ) {
		
		if(is_admin() && ! defined('DOING_AJAX')) {
			return $priceHTML;
		}
		
		$productManager = $this->getProductManagerForProduct( $product );
		
		// If no product manager for this product type found, return the original price HTML
		if ( is_null( $productManager ) ) {
			return $priceHTML;
		}
		
		$customPriceHTML = $productManager->getPriceHTML( $product );
		
		return is_null( $customPriceHTML ) ? $priceHTML : $customPriceHTML;
	}
	
	public function getProductManagerForProduct( WC_Product $product ) {
		foreach ( $this->productManagers as $productManager ) {
			try {
				$service = $this->getContainer()->get( $productManager );
				
				if ( $service->supports( $product ) ) {
					return $service;
				}
			} catch ( Exception $e ) {
				wc_doing_it_wrong( __METHOD__, $e->getMessage(), CustomPriceDisplayPlugin::VERSION );
			}
		}
		
		return null;
	}
}