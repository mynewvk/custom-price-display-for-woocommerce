<?php namespace CustomPriceDisplay;

use CustomPriceDisplay\Core\ServiceContainer;

class ProductPriceConfigProvider {
	
	public static function getPriceConfig( int $productId ): PriceConfig {
		
		$config = new PriceConfig( $productId );
		
		$settings = ServiceContainer::getInstance()->getSettings();
		
		$variableProductPriceSettings = $settings->getVariableProductsSection();
		
		$config->setProperty( 'price_display_format',
			$variableProductPriceSettings->getOptionValue( 'price_display_format',
				$config->getProperty( 'price_display_format' ) ) );
		$config->setProperty( 'price_suffix_prefix',
			$variableProductPriceSettings->getOptionValue( 'price_suffix_prefix',
				$config->getProperty( 'price_suffix_prefix' ) ) );
		$config->setProperty( 'custom_price_template',
			$variableProductPriceSettings->getOptionValue( 'custom_price_template',
				$config->getProperty( 'custom_price_template' ) ) );
		$config->setProperty( 'enable_for',
			$variableProductPriceSettings->getOptionValue( 'enable_for', $config->getProperty( 'enable_for' ) ) );
		$config->setProperty( 'selected_products_categories',
			$variableProductPriceSettings->getOptionValue( 'selected_products_categories',
				$config->getProperty( 'selected_products_categories' ) ) );
		$config->setProperty( 'update_variable_price_when_variation_selected',
			$variableProductPriceSettings->getOptionValue( 'update_variable_price_when_variation_selected',
				$config->getProperty( 'update_variable_price_when_variation_selected' ) ) );
		$config->setProperty( 'hide_variation_price',
			$variableProductPriceSettings->getOptionValue( 'hide_variation_price',
				$config->getProperty( 'hide_variation_price' ) ) );
		
		/**
		 * @hooked CustomPriceDisplay\Features\IndividualProductConfig\IndividualProductConfigFeature::applyIndividualProductConfig - 10
		 */
		return apply_filters( 'custom_price_display/price_config', $config, $productId );
	}
}