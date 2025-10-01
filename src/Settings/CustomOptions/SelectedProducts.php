<?php namespace CustomPriceDisplay\Settings\CustomOptions;

use CustomPriceDisplay\Services\LookupService;
use WC_Admin_Settings;

class SelectedProducts {

	const FIELD_TYPE = 'custom_price_display_selected_products';

	public function __construct() {
		add_action( 'woocommerce_admin_field_' . self::FIELD_TYPE, array( $this, 'render' ) );

		add_action( 'woocommerce_admin_settings_sanitize_option', function ( $value, $option, $rawValue ) {

			if ( self::FIELD_TYPE === $option['type'] ) {

				$value = is_array( $value ) ? $value : array();

				$value['included_products'] = isset( $value['included_products'] ) ? array_map( 'intval',
						$value['included_products'] ) : array();
				$value['excluded_products'] = isset( $value['excluded_products'] ) ? array_map( 'intval',
						$value['excluded_products'] ) : array();

				$value['included_categories'] = isset( $value['included_categories'] ) ? array_map( 'intval',
						$value['included_categories'] ) : array();
				$value['excluded_categories'] = isset( $value['excluded_categories'] ) ? array_map( 'intval',
						$value['excluded_categories'] ) : array();
			}

			return $value;
		}, 10, 3 );
	}

	public function render( $value ) {

		if ( ! isset( $value['id'] ) ) {
			$value['id'] = '';
		}

		if ( ! isset( $value['custom_attributes'] ) ) {
			$value['custom_attributes'] = array();
		} else {
			$value['custom_attributes'] = array_keys( $value['custom_attributes'] );
		}

		if ( ! isset( $value['title'] ) ) {
			$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
		}
		if ( ! isset( $value['default'] ) ) {
			$value['default'] = array(
					'included_products'   => array(),
					'excluded_products'   => array(),
					'included_categories' => array(),
					'excluded_categories' => array(),
			);
		}

		if ( ! isset( $value['value'] ) ) {
			$value['value'] = WC_Admin_Settings::get_option( $value['id'], $value['default'] );

			if ( ! is_array( $value['value'] ) ) {
				$value['value'] = array();
			}

			$value['value']['included_products']   = isset( $value['value']['included_products'] ) ? (array) $value['value']['included_products'] : array();
			$value['value']['excluded_products']   = isset( $value['value']['excluded_products'] ) ? (array) $value['value']['excluded_products'] : array();
			$value['value']['included_categories'] = isset( $value['value']['included_categories'] ) ? (array) $value['value']['included_categories'] : array();
			$value['value']['excluded_categories'] = isset( $value['value']['excluded_categories'] ) ? (array) $value['value']['excluded_categories'] : array();
		}

		$optionValue = $value['value'];
		?>

		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>

			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
				<div class="custom-price-display-selected-products">
					<div class="custom-price-display-selected-products-section">

						<div class="custom-price-display-selected-products-section__title">
							<?php esc_html_e( 'Included Products', 'custom-price-display-for-woocommerce' ); ?>
						</div>

						<div class="custom-price-display-selected-products-section__hint">
							<?php esc_html_e( 'If you do not specify products or product categories, the new prices format will apply to all variable products in your store (excluding those selected in the exclusions section).',
									'custom-price-display-for-woocommerce' ); ?>
						</div>

						<div class="custom-price-display-selected-products-section-row">

							<div class="custom-price-display-selected-products-section-row__label">
								<label>
									<?php esc_html_e( 'Apply for categories',
											'custom-price-display-for-woocommerce' ); ?>
								</label>
							</div>

							<div class="custom-price-display-selected-products-section-row__value">
								<select class="wc-product-search" multiple="true"
										style="width: 100%"
										name="<?php echo esc_attr( $value['id'] . '[included_categories][]' ); ?>"
										data-placeholder="<?php echo esc_attr( 'Search for a category…',
												'custom-price-display-for-woocommerce' ); ?>"
										data-action="<?php echo esc_attr( LookupService::CATEGORIES_SEARCH_ACTION ); ?>"
										data-minimum_input_length="1"
										tabindex="-1" aria-hidden="true">
									<?php foreach ( $optionValue['included_categories'] as $categoryId ): ?>

										<?php
										$term = get_term( $categoryId );

										if ( is_wp_error( $term ) || ! $term instanceof \WP_Term ) {
											continue;
										}
										?>

										<option value="<?php echo esc_attr( $categoryId ); ?>" selected>
											<?php echo esc_html( LookupService::getCategoryLabel( $term ) ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>

						</div>
						<div class="custom-price-display-selected-products-section-row">

							<div class="custom-price-display-selected-products-section-row__label">
								<label>
									<?php esc_html_e( 'Apply for products', 'custom-price-display-for-woocommerce' ); ?>
								</label>
							</div>
							<div class="custom-price-display-selected-products-section-row__value">
								<select class="wc-product-search" multiple=""
										style="width: 100%"
										name="<?php echo esc_attr( $value['id'] . '[included_products][]' ); ?>"
										data-placeholder="Search for a a product…"
										data-action="<?php echo esc_attr( LookupService::VARIABLE_PRODUCTS_SEARCH_ACTION ) ?>"
										data-minimum_input_length="1"
										tabindex="-1" aria-hidden="true">

									<?php foreach ( $optionValue['included_products'] as $productId ): ?>

										<?php
										$product = wc_get_product( $productId );

										if ( ! $product || ! $product->is_type( 'variable' ) ) {
											continue;
										}
										?>

										<option value="<?php echo esc_attr( $productId ); ?>" selected>
											<?php echo esc_html( $product->get_name() ); ?>
										</option>
									<?php endforeach; ?>

								</select>
							</div>
						</div>

						<div class="custom-price-display-selected-products-section__title">
							<?php esc_html_e( 'Excluded Products', 'custom-price-display-for-woocommerce' ); ?>
						</div>

						<div class="custom-price-display-selected-products-section-row">

							<div class="custom-price-display-selected-products-section-row__label">
								<label>
									<?php esc_html_e( 'Exclude for categories',
											'custom-price-display-for-woocommerce' ); ?>
								</label>
							</div>
							<div class="custom-price-display-selected-products-section-row__value">
								<select class="wc-product-search" multiple="true"
										style="width: 100%"
										name="<?php echo esc_attr( $value['id'] . '[excluded_categories][]' ); ?>"
										data-placeholder="<?php echo esc_attr( 'Search for a category…',
												'custom-price-display-for-woocommerce' ); ?>"
										data-action="<?php echo esc_attr( LookupService::CATEGORIES_SEARCH_ACTION ); ?>"
										data-minimum_input_length="1"
										tabindex="-1" aria-hidden="true">

									<?php foreach ( $optionValue['excluded_categories'] as $categoryId ): ?>

										<?php
										$term = get_term( $categoryId );

										if ( is_wp_error( $term ) || ! $term instanceof \WP_Term ) {
											continue;
										}
										?>

										<option value="<?php echo esc_attr( $categoryId ); ?>" selected>
											<?php echo esc_html( LookupService::getCategoryLabel( $term ) ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="custom-price-display-selected-products-section-row">

							<div class="custom-price-display-selected-products-section-row__label">
								<label>
									<?php esc_html_e( 'Exclude for products',
											'custom-price-display-for-woocommerce' ); ?>
								</label>
							</div>
							<div class="custom-price-display-selected-products-section-row__value">
								<select class="wc-product-search" multiple=""
										style="width: 100%"
										name="<?php echo esc_attr( $value['id'] . '[excluded_products][]' ); ?>"
										data-placeholder="Search for a a product…"
										data-action="<?php echo esc_attr( LookupService::VARIABLE_PRODUCTS_SEARCH_ACTION ) ?>"
										data-minimum_input_length="1"
										tabindex="-1" aria-hidden="true">

									<?php foreach ( $optionValue['excluded_products'] as $productId ): ?>

										<?php
										$product = wc_get_product( $productId );

										if ( ! $product || ! $product->is_type( 'variable' ) ) {
											continue;
										}
										?>

										<option value="<?php echo esc_attr( $productId ); ?>" selected>
											<?php echo esc_html( $product->get_name() ); ?>
										</option>
									<?php endforeach; ?>

								</select>
							</div>
						</div>

					</div>
				</div>
			</td>
		</tr>
		<?php
	}
}