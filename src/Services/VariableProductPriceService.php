<?php namespace CustomPriceDisplay\Services;

use CustomPriceDisplay\Core\ServiceContainerTrait;
use CustomPriceDisplay\CustomPriceDisplayPlugin;
use CustomPriceDisplay\ProductPriceConfigProvider;
use WC_Product;
use WC_Product_Variable;

class VariableProductPriceService {
	
	use ServiceContainerTrait;
	
	protected $includeUpdateVariablePriceWhenVariationSelectedScript = false;
	
	public function __construct() {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}
		
		add_filter( 'woocommerce_get_price_html', array( $this, 'formatPrice' ), 99, 2 );
		add_filter( 'wp_footer', array( $this, 'hideIndividualPrices' ), 9999, 2 );
		add_filter( 'woocommerce_get_price_html', array( $this, 'wrapVariablePriceToUpdateWhenVariationSelected' ),
			99999, 2 );
		
		add_action( 'wp_footer', function () {
			if ( $this->includeUpdateVariablePriceWhenVariationSelectedScript ) {
				wp_enqueue_script( 'custom-price-display__variable-product-price',
					$this->getContainer()->getFileManager()->locateJSAsset( 'frontend/variable-product-price' ),
					array( 'jquery' ), CustomPriceDisplayPlugin::VERSION, true );
			}
		}, - 999 );
	}
	
	public function wrapVariablePriceToUpdateWhenVariationSelected(
		?string $defaultPriceHTML,
		?WC_Product $product
	): ?string {
		if ( ! $product ) {
			return $defaultPriceHTML;
		}
		if ( is_cart() ) {
			return $defaultPriceHTML;
		}
		if ( ! ( $product instanceof WC_Product_Variable ) ) {
			return $defaultPriceHTML;
		}
		$productPriceConfig = ProductPriceConfigProvider::getPriceConfig( $product->get_id() );
		
		if ( 'yes' !== $productPriceConfig->getProperty( 'update_variable_price_when_variation_selected' ) ) {
			return $defaultPriceHTML;
		}
		
		$this->includeUpdateVariablePriceWhenVariationSelectedScript = true;
		
		return apply_filters( 'custom_price_display/variable_product_price/wrapped_price',
			'<span class="cpd-variable-product-price" data-product-id="' . $product->get_id() . '">' . $defaultPriceHTML . '</span >',
			$product, $productPriceConfig, $defaultPriceHTML );
	}
	
	public function hideIndividualPrices() {
		global $product;
		
		if ( ! ( $product instanceof WC_Product_Variable ) ) {
			return;
		}
		
		if ( is_cart() || is_checkout() ) {
			return;
		}
		
		$productPriceConfig = ProductPriceConfigProvider::getPriceConfig( $product->get_id() );
		
		if ( 'yes' !== $productPriceConfig->getProperty( 'hide_variation_price' ) ) {
			return;
		}
		
		?>
		<style>
			.woocommerce-variation-price {
				display: none !important;
			}
		</style>
		<?php
	}
	
	public function formatPrice( ?string $defaultPriceHTML, ?WC_Product $product ): ?string {
		
		if ( ! $product ) {
			return $defaultPriceHTML;
		}
		
		// Some themes use ->get_price_html() to show cart item price. Do not modify product price if we're in the cart
		if ( is_cart() ) {
			return $defaultPriceHTML;
		}
		
		// Do not modify prices for products that are not variable
		if ( ! ( $product instanceof WC_Product_Variable ) ) {
			return $defaultPriceHTML;
		}
		
		$productPriceConfig = ProductPriceConfigProvider::getPriceConfig( $product->get_id() );
		
		if ( ! $productPriceConfig->isActiveForProduct() ) {
			return $defaultPriceHTML;
		}
		
		if ( 'default' === $productPriceConfig->getProperty( 'price_display_format' ) ) {
			return $defaultPriceHTML;
		}
		
		if ( 'lowest' === $productPriceConfig->getProperty( 'price_display_format' ) ) {
			$basePrice = $product->get_variation_price( 'min', true );
		} elseif ( 'highest' === $productPriceConfig->getProperty( 'price_display_format' ) ) {
			$basePrice = $product->get_variation_price( 'max', true );
		} elseif ( 'custom' === $productPriceConfig->getProperty( 'price_display_format' ) ) {
			
			if ( cpd_fs()->can_use_premium_code__premium_only() ) {
				
				$priceTemplate = $productPriceConfig->getProperty( 'custom_price_template' );
				
				$priceHTML = str_replace( [ '{cpd_lowest_price}', '{cpd_highest_price}' ], [
					wc_price( $product->get_variation_price( 'min', true ) ),
					wc_price( $product->get_variation_price( 'max', true ) ),
				], $priceTemplate );
			} else {
				return $defaultPriceHTML;
			}
			
		} else {
			return $defaultPriceHTML;
		}
		
		if ( ! isset( $priceHTML ) ) {
			$priceSuffixPrefix = $productPriceConfig->getProperty( 'price_suffix_prefix' );
			$suffix            = isset( $priceSuffixPrefix['suffix'] ) ? $priceSuffixPrefix['suffix'] : '';
			$prefix            = isset( $priceSuffixPrefix['prefix'] ) ? $priceSuffixPrefix['prefix'] : '';
			
			$priceHTML = wc_price( $basePrice );
			
			$priceHTML = $prefix . ' ' . $priceHTML . ' ' . $suffix . ' ' . $product->get_price_suffix();
		}
		
		return apply_filters( 'custom_price_display/variable_product_price/price_html', $priceHTML, $product,
			$productPriceConfig, $defaultPriceHTML );
	}
}