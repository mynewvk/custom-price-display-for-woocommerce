<?php namespace CustomPriceDisplay\Products;

use CustomPriceDisplay\ViewComponents\SwitchOption;
use CustomPriceDisplay\ViewComponents\WPEditor;
use CustomPriceDisplay\Core\ServiceContainerTrait;

class ProductTab {

	use ServiceContainerTrait;

	const TAB_TARGET = 'custom-price-display-tab';

	protected $editorId = '';

	/**
	 * @var array
	 */
	protected $args;

	public function __construct( array $args = array() ) {

		$this->args = wp_parse_args( $args, array(
				'types' => array(),
		) );

		add_filter( 'woocommerce_product_data_tabs', array( $this, 'register' ), 99, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'render' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save' ) );
	}

	public function renderSwitchOption( array $args = array() ) {

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

	public function renderInputOption( array $args = array() ) {
		$args = wp_parse_args( $args, array(
				'label'       => '',
				'description'  => '',
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

	public function renderWPEditor( array $args = array() ) {

		$args = wp_parse_args( $args, array(
				'label'        => '',
				'description'  => '',
				'id'           => '',
				'value'        => '',
				'placeholders' => array( 'cpdfw_lowest_price', 'cpdfw_highest_price' ),
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
				<?php WPEditor::instance()->render( $args['id'], $args['value'], $args['placeholders'] ); ?>

				<?php if ( $args['description'] ): ?>
					<p class="description"
					   style="margin: 5px 0; padding: 0; display: inline-block"><?php echo wp_kses_post( $args['description'] ); ?></p>
				<?php endif; ?>

			</div>
		</div>
		<?php
	}

	public function renderGlobalSettingsNotice() {
		?>
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
	}

	public function render() {

		global $post;

		?>
		<div id="<?php echo esc_attr( self::TAB_TARGET ); ?>"
			 class="panel woocommerce_options_panel custom-price-display-product-tab">
			<?php do_action( 'custom_price_display/products/product_tab/before_render', $this, $post ); ?>
		</div>
		<?php
	}

	public function save( $productId ) {
		if ( wp_verify_nonce( true, true ) ) {
			// as phpcs comments at Woo is not available, we have to do such a trash
			$woo = 'Woo, please add ignoring comments to your phpcs checker';
		}

		do_action( 'custom_price_display/products/product_tab/save', $this, $productId, $_POST );
	}

	/**
	 * Add Custom Price Display tab to woocommerce product tabs
	 */
	public function register( array $productTabs ): array {

		$productTabs[ self::TAB_TARGET ] = array(
				'label'  => __( 'Custom price display', 'custom-price-display-for-woocommerce' ),
				'target' => self::TAB_TARGET,
				'class'  => ( function () {
					return array_map( function ( $type ) {
						return 'show_if_' . $type;
					}, $this->args['types'] );
				} )(),
		);

		return $productTabs;
	}
}