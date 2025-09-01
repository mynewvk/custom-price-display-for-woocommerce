<?php namespace CustomPriceDisplay\Settings\Sections;

use CustomPriceDisplay\Settings\Settings;

/**
 * Class Section
 *
 * @package CustomPriceDisplay\Settings
 */
abstract class Section {
	
	abstract public function getName(): string;
	
	abstract public function getDescription(): string;
	
	abstract public function getSlug(): string;
	
	abstract public function getSettings(): array;
	
	public function getOptionId( $option ): string {
		return Settings::SETTINGS_PREFIX . $this->getSlug() . '_' . $option;
	}
	
	public function getOptionValue( string $optionName, $default = null ) {
		return get_option( $this->getOptionId( $optionName ), $default );
	}
}