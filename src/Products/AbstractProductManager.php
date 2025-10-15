<?php namespace CustomPriceDisplay\Products;

use WC_Product;

abstract class AbstractProductManager {
	
	abstract public function getPriceHTML( WC_Product $product ): ?string;
	
	abstract public function getSupportedProductTypes(): array;
	
	public function supports( WC_Product $product ): bool {
		return in_array( $product->get_type(), $this->getSupportedProductTypes() );
	}
	
	public function isEnabledForProduct( $config, $productId ): bool {
		
		if ( 'all' === $config['enable_for'] ) {
			return true;
		}
		
		$selected = $config( 'selected_products_categories' );
		
		if ( in_array( $productId, $selected['excluded_products'], true ) ) {
			return false;
		}
		
		if ( in_array( $productId, $selected['included_products'], true ) ) {
			return true;
		}
		
		if ( empty( $selected['included_categories'] ) && empty( $selected['excluded_categories'] ) ) {
			// Works for all products if none categories are selected
			return true;
		}
		
		$productCategories = wc_get_product_cat_ids( $productId );
		
		foreach ( $productCategories as $categoryId ) {
			if ( in_array( $categoryId, $selected['excluded_categories'], true ) ) {
				return false;
			}
			
			if ( in_array( $categoryId, $selected['included_categories'], true ) ) {
				return true;
			}
		}
		
		return false;
	}
	
	abstract public function init(): void;
	
}