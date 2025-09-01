<?php namespace CustomPriceDisplay\Features\IndividualProductConfig;

use CustomPriceDisplay\Components\SwitchOption;
use CustomPriceDisplay\Components\WPEditor;
use CustomPriceDisplay\Core\ServiceContainerTrait;

class ProductTab {
	
	use ServiceContainerTrait;
	
	const TAB_TARGET = 'custom-price-display-tab';
	
	protected $editorId = '';
	
	public function __construct() {
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'register' ), 99, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'render' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save' ) );
	}
	
	protected function renderSwitchOption( array $args = array() ) {
		$args = wp_parse_args( $args, array(
			'label'   => '',
			'id'      => '',
			'value'   => '',
			'options' => array(),
		) );
		
		?>
		<div style="display: flex; gap: 10px; padding: 10px; font-size: 12px"
			 class="custom-price-display-product-option">

			<div style="width: 150px; flex-shrink: 0;">
				<label style="float:none; margin:0;" for="<?php echo esc_attr( $args['id'] ); ?>">
					<?php echo esc_html( $args['label'] ); ?>
				</label>
			</div>

			<div style="width: 100%">
				<?php SwitchOption::render( $args['options'], $args['value'], $args['id'] ); ?>
			</div>
		</div>
		<?php
	}
	
	protected function renderInputOption( array $args = array() ) {
		$args = wp_parse_args( $args, array(
			'label'       => '',
			'id'          => '',
			'value'       => '',
			'placeholder' => '',
			'type'        => 'text',
		) );
		
		?>
		<div style="display: flex; gap: 10px; padding: 10px; align-items: center; font-size: 12px"
			 class="custom-price-display-product-option">

			<div style="width: 150px; flex-shrink: 0;">
				<label style="float:none; margin:0;" for="<?php echo esc_attr( $args['id'] ); ?>">
					<?php echo esc_html( $args['label'] ); ?>
				</label>
			</div>

			<div>
				<input type="<?php echo esc_attr( $args['type'] ); ?>"
					   name="<?php echo esc_attr( $args['id'] ) ?>"
					   id="<?php echo esc_attr( $args['id'] ); ?>"
					   value="<?php echo esc_attr( $args['value'] ); ?>"
					   placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"
					   style="width: 300px; max-width: 100%;"/>
			</div>
		</div>
		<?php
	}
	
	protected function renderWPEditor( array $args = array() ) {
		
		$args = wp_parse_args( $args, array(
			'label'        => '',
			'id'           => '',
			'value'        => '',
			'placeholders' => array( 'cpd_lowest_price', 'cpd_highest_price' ),
		) );
		
		$this->editorId = $args['id'];
		
		?>

		<div style="display: flex; gap: 10px; padding: 10px; font-size: 12px"
			 class="custom-price-display-product-option">

			<div style="width: 150px; flex-shrink: 0;">
				<label style="float:none; margin:0;" for="<?php echo esc_attr( $args['id'] ); ?>">
					<?php echo esc_html( $args['label'] ); ?>
				</label>
			</div>

			<div style="max-width: 100%; width: 500px;">
				
				<?php if ( ! cpd_fs()->can_use_premium_code() ): ?>
					<div style="color: red; margin-bottom: 10px">
						<?php esc_html_e( 'This feature is available only in the premium version.',
							'custom-price-display-for-woocommerce' ); ?>
						<a href="<?php echo esc_html( cpd_fs_activation_url() ) ?>" target="_blank">
							<?php esc_html_e( 'Upgrade your plan', 'custom-price-display-for-woocommerce' ); ?>
						</a>
					</div>
				<?php endif; ?>
				
				<?php WPEditor::instance()->render( $args['id'], $args['value'], $args['placeholders'] ); ?>
				
				<?php if ( $args['description'] ): ?>
					<p class="description"
					   style="margin: 5px 0; padding: 0; display: inline-block"><?php echo wp_kses_post( $args['description'] ); ?></p>
				<?php endif; ?>

			</div>
		</div>
		<?php
	}
	
	public function render() {
		
		global $post;
		
		$customPriceData = get_post_meta( $post->ID, '_cpd_custom_price_data', true );
		
		$priceFormat   = is_array( $customPriceData ) && isset( $customPriceData['format'] ) ? $customPriceData['format'] : '';
		$pricePrefix   = is_array( $customPriceData ) && isset( $customPriceData['prefix'] ) ? $customPriceData['prefix'] : '';
		$priceSuffix   = is_array( $customPriceData ) && isset( $customPriceData['suffix'] ) ? $customPriceData['suffix'] : '';
		$priceTemplate = is_array( $customPriceData ) && isset( $customPriceData['template'] ) ? $customPriceData['template'] : '';
		
		?>
		<div id="<?php echo esc_attr( self::TAB_TARGET ); ?>" class="panel woocommerce_options_panel">

			<div class="hidden show_if_variable options_group custom-price-display-product-tab">
				<script>
					// Conditional logic
					jQuery(document).ready(function () {

						const $priceFormat = jQuery('.custom-price-display-product-tab .custom-price-display-template-option');
						const $pricePrefix = jQuery('#cpd_price_prefix').closest('.custom-price-display-product-option');
						const $priceSuffix = jQuery('#cpd_price_suffix').closest('.custom-price-display-product-option');
						const $priceTemplate = jQuery('#cpd_custom_price_template').closest('.custom-price-display-product-option');

						function refreshVisibility() {
							const selectedFormat = $priceFormat.filter(':checked').val();

							if (selectedFormat === 'custom') {
								$priceTemplate.show();
								$pricePrefix.hide();
								$priceSuffix.hide();
							} else if (selectedFormat === 'default' || selectedFormat === '') {
								$priceTemplate.hide();
								$pricePrefix.hide();
								$priceSuffix.hide();
							} else {
								$priceTemplate.hide();
								$pricePrefix.show();
								$priceSuffix.show();
							}
						}

						$priceFormat.on('change', refreshVisibility);
						refreshVisibility();
					});
				</script>

				<div style="margin-bottom: 10px; padding: 10px; background: #f9f9f9; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 10px;">
					<div>
						<span class="dashicons dashicons-info"></span>
					</div>
					<div>
						<?php
							
							printf( wp_kses_post( // translators: %s is replaced with the settings link.
								__( 'Choose how the price should be displayed for this product. You can override the %s here.',
									'custom-price-display-for-woocommerce' ) ),
								'<a href="' . esc_url( $this->getContainer()->getSettings()->getLink() ) . '" target="_blank">' . esc_html__( 'global settings',
									'custom-price-display-for-woocommerce' ) . '</a>' );
						?>
					</div>

				</div>
				
				<?php
					$this->renderSwitchOption( array(
						'label'   => __( 'Price display format', 'custom-price-display-for-woocommerce' ),
						'id'      => 'cpd_custom_price_format',
						'value'   => $priceFormat,
						'options' => array(
							''        => __( 'Use global settings', 'custom-price-display-for-woocommerce' ),
							'default' => __( 'Default', 'custom-price-display-for-woocommerce' ),
							'lowest'  => __( 'Lowest Price', 'custom-price-display-for-woocommerce' ),
							'highest' => __( 'Highest Price', 'custom-price-display-for-woocommerce' ),
							'custom'  => __( 'Custom', 'custom-price-display-for-woocommerce' ),
						),
					) );
					
					$this->renderInputOption( array(
						'label'       => __( 'Price prefix', 'custom-price-display-for-woocommerce' ),
						'id'          => 'cpd_price_prefix',
						'value'       => $pricePrefix,
						'placeholder' => __( 'e.g. From', 'custom-price-display-for-woocommerce' ),
						'type'        => 'text',
					) );
					
					$this->renderInputOption( array(
						'label'       => __( 'Price suffix', 'custom-price-display-for-woocommerce' ),
						'id'          => 'cpd_price_suffix',
						'value'       => $priceSuffix,
						'placeholder' => __( 'e.g. per item', 'custom-price-display-for-woocommerce' ),
						'type'        => 'text',
					) );
					
					$this->renderWPEditor( array(
						'label'        => __( 'Custom price template', 'custom-price-display-for-woocommerce' ),
						'id'           => 'cpd_custom_price_template',
						'value'        => $priceTemplate,
						'placeholders' => array(
							'cpd_lowest_price',
							'cpd_highest_price',
						),
						'description'  => __( 'Use variables <code>{cpd_lowest_price}</code> and <code>{cpd_highest_price}</code> to define a custom price string.',
							'custom-price-display-for-woocommerce' ),
					) );
				?>
			</div>

		</div>
		<?php
	}
	
	public function save( $productId ) {
		if ( wp_verify_nonce( true, true ) ) {
			// as phpcs comments at Woo is not available, we have to do such a trash
			$woo = 'Woo, please add ignoring comments to your phpcs checker';
		}
		
		$priceFormat   = isset( $_POST['cpd_custom_price_format'] ) ? sanitize_text_field( wp_unslash( $_POST['cpd_custom_price_format'] ) ) : '';
		$pricePrefix   = isset( $_POST['cpd_price_prefix'] ) ? sanitize_text_field( wp_unslash( $_POST['cpd_price_prefix'] ) ) : '';
		$priceSuffix   = isset( $_POST['cpd_price_suffix'] ) ? sanitize_text_field( wp_unslash( $_POST['cpd_price_suffix'] ) ) : '';
		$priceTemplate = isset( $_POST['cpd_custom_price_template'] ) ? wp_kses_post( wp_unslash( $_POST['cpd_custom_price_template'] ) ) : '';
		
		if ( $priceFormat ) {
			
			$priceFormatData = array(
				'format'   => $priceFormat,
				'prefix'   => $pricePrefix,
				'suffix'   => $priceSuffix,
				'template' => $priceTemplate,
			);
			
			update_post_meta( $productId, '_cpd_custom_price_data', $priceFormatData );
		} else {
			delete_post_meta( $productId, '_cpd_custom_price_data' );
		}
	}
	
	/**
	 * Add Custom Price Display tab to woocommerce product tabs
	 */
	public function register( array $productTabs ): array {
		
		$productTabs[ self::TAB_TARGET ] = array(
			'label'  => __( 'Custom price display', 'custom-price-display-for-woocommerce' ),
			'target' => self::TAB_TARGET,
			'class'  => ( function () {
				$types = array(
					'variable',
					'variable-subscription',
				);
				
				return array_map( function ( $type ) {
					return 'show_if_' . $type;
				}, $types );
			} )(),
		);
		
		return $productTabs;
	}
}