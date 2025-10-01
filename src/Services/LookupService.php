<?php namespace CustomPriceDisplay\Services;

use CustomPriceDisplay\Core\ServiceContainerTrait;
use WP_Term;

class LookupService {
	
	use ServiceContainerTrait;
	
	const CATEGORIES_SEARCH_ACTION = 'woocommerce_json_search_cpdfw_categories';
	const VARIABLE_PRODUCTS_SEARCH_ACTION = 'woocommerce_json_search_cpdfw_variable_products';
	
	public function __construct() {
		add_action( 'wp_ajax_' . self::CATEGORIES_SEARCH_ACTION, array( $this, 'categoriesSearchHandler' ) );
		add_action( 'wp_ajax_' . self::VARIABLE_PRODUCTS_SEARCH_ACTION,
			array( $this, 'searchVariableProductsHandler' ) );
	}
	
	public function categoriesSearchHandler() {
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json( array() );
		}
		
		if ( ! check_ajax_referer( 'search-products', 'security', false ) ) {
			wp_send_json( array() );
		}
		
		$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : false;
		
		if ( ! $term || strlen( $term ) < 1 ) {
			wp_send_json( array() );
		}
		
		$args = array(
			'taxonomy'   => array( 'product_cat' ),
			'orderby'    => 'id',
			'order'      => 'ASC',
			'limit'      => 5,
			'hide_empty' => false,
			'fields'     => 'all',
			'name__like' => $term,
		);
		
		$terms = get_terms( $args );
		
		if ( $terms ) {
			$_terms = array();
			
			foreach ( $terms as $term ) {
				if ( $term instanceof WP_Term ) {
					$_terms[ $term->term_id ] = self::getCategoryLabel( $term );
				}
			}
			
			wp_send_json( $_terms );
		}
	}
	
	public function searchVariableProductsHandler() {
		
		ob_start();
		
		if ( ! check_ajax_referer( 'search-products', 'security', false ) ) {
			wp_send_json( array() );
		}
		
		$term = (string) wc_clean( sanitize_text_field( wp_unslash( $_GET['term'] ?? '' ) ) );
		
		if ( empty( $term ) ) {
			wp_die();
		}
		
		$args = array(
			'status' => 'publish',
			'type'   => array( 'variable' ),
			'limit'  => - 1,
			'return' => 'ids',
			'search' => '*' . $term . '*',
		);
		
		$products       = wc_get_products( $args );
		$found_products = array();
		
		foreach ( $products as $product_id ) {
			$product = wc_get_product( $product_id );
			
			if ( $product && $product->is_type( 'variable' ) ) {
				$found_products[ $product_id ] = rawurldecode( $product->get_formatted_name() );
			}
		}
		
		wp_send_json( $found_products );
	}
	
	public static function getCategoryLabel( WP_Term $category ): string {
		$parentTermName = '';
		
		if ( $category->parent ) {
			$parentTerm = get_term( $category->parent );
			
			if ( $parentTerm ) {
				$parentTermName = ' (' . $parentTerm->name . ')';
			}
		}
		
		return $category->name . $parentTermName;
	}
}
