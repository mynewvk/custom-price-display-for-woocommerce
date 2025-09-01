<?php namespace CustomPriceDisplay\Settings\Sections;

use CustomPriceDisplay\Settings\CustomOptions\Checkbox;
use CustomPriceDisplay\Settings\CustomOptions\MultipleTextInputs;
use CustomPriceDisplay\Settings\CustomOptions\RichText;
use CustomPriceDisplay\Settings\CustomOptions\SelectedProducts;
use CustomPriceDisplay\Settings\CustomOptions\SwitchOption;

/**
 * Class VariableProducts
 *
 * @package CustomPriceDisplay\Settings
 */
class VariableProducts extends Section {
	
	public function getSettings(): array {
		return array(
			array(
				'title'   => __( 'Price display format', 'custom-price-display-for-woocommerce' ),
				'id'      => $this->getOptionId( 'price_display_format' ),
				'type'    => SwitchOption::FIELD_TYPE,
				'options' => array(
					'default' => __( 'Default', 'custom-price-display-for-woocommerce' ),
					'lowest'  => __( 'Lowest price', 'custom-price-display-for-woocommerce' ),
					'highest' => __( 'Highest price', 'custom-price-display-for-woocommerce' ),
					'custom'  => __( 'Custom', 'custom-price-display-for-woocommerce' ),
				),
				'default' => 'lowest',
				'desc'    => __( 'Choose how to display the price range for variable products. The custom option allows you to set a custom format in the next setting.',
					'custom-price-display-for-woocommerce' ),
			),
			array(
				'title'        => __( 'Text before & after price', 'custom-price-display-for-woocommerce' ),
				'id'           => $this->getOptionId( 'price_suffix_prefix' ),
				'type'         => MultipleTextInputs::FIELD_TYPE,
				'options'      => array(
					'prefix' => array(
						'label'       => __( 'Prefix', 'custom-price-display-for-woocommerce' ),
						'placeholder' => __( 'e.g. From', 'custom-price-display-for-woocommerce' ),
					),
					'suffix' => array(
						'label'       => __( 'Suffix', 'custom-price-display-for-woocommerce' ),
						'placeholder' => __( 'e.g. per item', 'custom-price-display-for-woocommerce' ),
					),
				),
				'default'      => array(
					'prefix' => __( 'From ', 'custom-price-display-for-woocommerce' ),
					'suffix' => '',
				),
				'desc'         => __( 'Those texts will be displayed before and after the product price.',
					'custom-price-display-for-woocommerce' ),
				'conditionals' => array(
					array(
						'id'    => $this->getOptionId( 'price_display_format' ),
						'value' => array( 'lowest', 'highest' ),
					),
				),
			),
			array(
				'title'        => __( 'Custom price template', 'custom-price-display-for-woocommerce' ),
				'id'           => $this->getOptionId( 'custom_price_template' ),
				'type'         => RichText::FIELD_TYPE,
				'placeholders' => array(
					'cpd_lowest_price',
					'cpd_highest_price',
				),
				'default'      => __( 'From {cpd_lowest_price}', 'custom-price-display-for-woocommerce' ),
				'desc'         => __( 'Use variables <code>cpd_lowest_price</code> and <code>cpd_highest_price</code> to define custom price string.',
					'custom-price-display-for-woocommerce' ),
				'conditionals' => array(
					array(
						'id'    => $this->getOptionId( 'price_display_format' ),
						'value' => array( 'custom' ),
					),
				),
			),
			array(
				'title'        => __( 'Enable for', 'custom-price-display-for-woocommerce' ),
				'id'           => $this->getOptionId( 'enable_for' ),
				'type'         => SwitchOption::FIELD_TYPE,
				'options'      => array(
					'all'      => __( 'All products', 'custom-price-display-for-woocommerce' ),
					'selected' => __( 'Selected products & categories', 'custom-price-display-for-woocommerce' ),
				),
				'default'      => 'all',
				'conditionals' => array(
					
					array(
						'id'    => $this->getOptionId( 'price_display_format' ),
						'value' => array( 'custom', 'lowest', 'highest' ),
					),
				
				),
			),
			array(
				'title'        => __( 'Selected products & categories', 'custom-price-display-for-woocommerce' ),
				'id'           => $this->getOptionId( 'selected_products_categories' ),
				'type'         => SelectedProducts::FIELD_TYPE,
				'default'      => array(
					'included_products'   => array(),
					'excluded_products'   => array(),
					'included_categories' => array(),
					'excluded_categories' => array(),
				),
				'conditionals' => array(
					
					array(
						'id'    => $this->getOptionId( 'enable_for' ),
						'value' => 'selected',
					),
				
				),
			),
			array(
				'title'   => __( 'Update main variable product price when a variation is selected',
					'custom-price-display-for-woocommerce' ),
				'id'      => $this->getOptionId( 'update_variable_price_when_variation_selected' ),
				'type'    => Checkbox::FIELD_TYPE,
				'default' => 'no',
				'desc'    => __( 'When enabled, the main product price updates to match the selected variation. When disabled, the main price stays fixed.',
					'custom-price-display-for-woocommerce' ),
			),
			array(
				'title'   => __( 'Hide individual variation prices', 'custom-price-display-for-woocommerce' ),
				'id'      => $this->getOptionId( 'hide_variation_price' ),
				'type'    => Checkbox::FIELD_TYPE,
				'default' => 'no',
				'desc'    => __( 'Hides prices of individual variations. Only the main product price will be shown.',
					'custom-price-display-for-woocommerce' ),
			),
		);
	}
	
	public function getSlug(): string {
		return 'variable_products';
	}
	
	public function getName(): string {
		return __( 'Variable Products', 'custom-price-display-for-woocommerce' );
	}
	
	public function getDescription(): string {
		return __( 'Fine tune the price display for variable products.', 'custom-price-display-for-woocommerce' );
	}
}