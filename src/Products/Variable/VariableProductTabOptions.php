<?php namespace CustomPriceDisplay\Products\Variable;

use CustomPriceDisplay\Core\ServiceContainerTrait;
use CustomPriceDisplay\Products\ProductTab;

class VariableProductTabOptions {

	use ServiceContainerTrait;

	protected $productManager;

	public function __construct( $productManager ) {
		$this->productManager = $productManager;

		add_action( 'custom_price_display/products/product_tab/before_render', array( $this, 'render' ), 10, 2 );
		add_action( 'custom_price_display/products/product_tab/save', array( $this, 'save' ), 10, 3 );
		add_filter( 'custom_price_display/products/variable/config', array( $this, 'loadSettings' ), 20, 2 );
	}

	public function render( ProductTab $tab, \WP_Post $post ) {
		$customPriceData = get_post_meta( $post->ID, '_cpdfw_variable_custom_price_data', true );

		$priceFormat   = is_array( $customPriceData ) && isset( $customPriceData['format'] ) ? $customPriceData['format'] : '';
		$pricePrefix   = is_array( $customPriceData ) && isset( $customPriceData['prefix'] ) ? $customPriceData['prefix'] : '';
		$priceSuffix   = is_array( $customPriceData ) && isset( $customPriceData['suffix'] ) ? $customPriceData['suffix'] : '';
		$priceTemplate = is_array( $customPriceData ) && isset( $customPriceData['template'] ) ? $customPriceData['template'] : '';

		$classes = implode( ' ', array_map( function ( $type ) {
			return 'show_if_' . $type;
		}, $this->productManager->getSupportedProductTypes() ) );

		?>

		<div class="hidden <?php echo esc_attr( $classes ); ?> options_group">

			<?php
				$tab->renderGlobalSettingsNotice();

				$tab->renderSwitchOption( array(
						'label'   => __( 'Price display format', 'custom-price-display-for-woocommerce' ),
						'id'      => 'cpdfw_variable_product_custom_price_format',
						'value'   => $priceFormat,
						'options' => array(
								''        => __( 'Use global settings', 'custom-price-display-for-woocommerce' ),
								'default' => __( 'Default', 'custom-price-display-for-woocommerce' ),
								'lowest'  => __( 'Lowest Price', 'custom-price-display-for-woocommerce' ),
								'highest' => __( 'Highest Price', 'custom-price-display-for-woocommerce' ),
								'custom'  => __( 'Custom', 'custom-price-display-for-woocommerce' ),
						),
				) );

				$tab->renderInputOption( array(
						'label'       => __( 'Price prefix', 'custom-price-display-for-woocommerce' ),
						'id'          => 'cpdfw_variable_product_price_prefix',
						'value'       => $pricePrefix,
						'placeholder' => __( 'e.g. From', 'custom-price-display-for-woocommerce' ),
						'type'        => 'text',
				) );

				$tab->renderInputOption( array(
						'label'       => __( 'Price suffix', 'custom-price-display-for-woocommerce' ),
						'id'          => 'cpdfw_variable_product_price_suffix',
						'value'       => $priceSuffix,
						'placeholder' => __( 'e.g. per item', 'custom-price-display-for-woocommerce' ),
						'type'        => 'text',
				) );

				$tab->renderWPEditor( array(
						'label'        => __( 'Custom price template', 'custom-price-display-for-woocommerce' ),
						'id'           => 'cpdfw_variable_product_custom_price_template',
						'value'        => $priceTemplate,
						'placeholders' => array(
								'cpdfw_lowest_price',
								'cpdfw_highest_price',
						),
						'description'  => __( 'Use variables <code>{cpdfw_lowest_price}</code> and <code>{cpdfw_highest_price}</code> to define a custom price string.',
								'custom-price-display-for-woocommerce' ),
				) );
			?>
		</div>
		<?php
	}

	public function save( $tab, $productId, $data ) {
		$priceFormat   = isset( $data['cpdfw_variable_product_custom_price_format'] ) ? sanitize_text_field( wp_unslash( $data['cpdfw_variable_product_custom_price_format'] ) ) : '';
		$pricePrefix   = isset( $data['cpdfw_variable_product_price_prefix'] ) ? sanitize_text_field( wp_unslash( $data['cpdfw_variable_product_price_prefix'] ) ) : '';
		$priceSuffix   = isset( $data['cpdfw_variable_product_price_suffix'] ) ? sanitize_text_field( wp_unslash( $data['cpdfw_variable_product_price_suffix'] ) ) : '';
		$priceTemplate = isset( $data['cpdfw_variable_product_custom_price_template'] ) ? wp_kses_post( wp_unslash( $data['cpdfw_variable_product_custom_price_template'] ) ) : '';

		if ( $priceFormat ) {

			$priceFormatData = array(
					'format'   => $priceFormat,
					'prefix'   => $pricePrefix,
					'suffix'   => $priceSuffix,
					'template' => $priceTemplate,
			);

			update_post_meta( $productId, '_cpdfw_variable_custom_price_data', $priceFormatData );
		} else {
			delete_post_meta( $productId, '_cpdfw_variable_custom_price_data' );
		}
	}

	public function loadSettings( $config, $productId ): array {

		$productData = get_post_meta( $productId, '_cpdfw_variable_custom_price_data', true );

		if ( is_array( $productData ) ) {

			if ( isset( $productData['format'] ) && $productData['format'] ) {
				$config['price_display_format'] = $productData['format'];
			} else {
				return $config;
			}

			if ( isset( $productData['prefix'] ) ) {
				$config['price_suffix_prefix']['prefix'] = $productData['prefix'];
			}

			if ( isset( $productData['suffix'] ) ) {
				$config['price_suffix_prefix']['suffix'] = $productData['suffix'];
			}

			if ( isset( $productData['template'] ) && $productData['template'] ) {
				$config['custom_price_template'] = $productData['template'];
			}
		}

		return $config;
	}
}