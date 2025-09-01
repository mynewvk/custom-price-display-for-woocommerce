<?php namespace CustomPriceDisplay;

use InvalidArgumentException;

class PriceConfig {
	
	protected $data = array(
		'price_display_format'                          => 'lowest',
		'price_suffix_prefix'                           => array(
			'prefix' => '',
			'suffix' => '',
		),
		'custom_price_template'                         => '',
		'enable_for'                                    => 'all',
		'selected_products_categories'                  => array(
			'included_products'   => array(),
			'excluded_products'   => array(),
			'included_categories' => array(),
			'excluded_categories' => array(),
		),
		'update_variable_price_when_variation_selected' => false,
		'hide_variation_price'                          => false,
	);
	
	/**
	 * @var int
	 */
	protected $productId;
	
	public function __construct( int $productId ) {
		$this->productId = $productId;
	}
	
	public function setProperty( $name, $value ) {
		if ( array_key_exists( $name, $this->data ) ) {
			$this->data[ $name ] = $value;
		} else {
			throw new InvalidArgumentException( esc_html__( 'Property $name does not exist in PriceConfig.',
				'custom-price-display-for-woocommerce' ) );
		}
	}
	
	public function getProperty( $name ) {
		if ( array_key_exists( $name, $this->data ) ) {
			return $this->data[ $name ];
		} else {
			throw new InvalidArgumentException( esc_html__( 'Property $name does not exist in PriceConfig.',
				'custom-price-display-for-woocommerce' ) );
		}
	}
	
	public function isActiveForProduct(): bool {
		
		if ( cpd_fs()->can_use_premium_code__premium_only() ) {
			
			if ( 'all' === $this->getProperty( 'enable_for' ) ) {
				return true;
			}
			
			$selected = $this->getProperty( 'selected_products_categories' );
			
			if ( in_array( $this->getProductId(), $selected['excluded_products'], true ) ) {
				return false;
			}
			
			if ( in_array( $this->getProductId(), $selected['included_products'], true ) ) {
				return true;
			}
			
			if ( empty( $selected['included_categories'] ) && empty( $selected['excluded_categories'] ) ) {
				// Works for all products if none categories are selected
				return true;
			}
			
			$productCategories = wc_get_product_cat_ids( $this->getProductId() );
			
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
		
		return true;
	}
	
	public function getProductId(): int {
		return $this->productId;
	}
}