<?php namespace CustomPriceDisplay\Features;

use CustomPriceDisplay\Features\IndividualProductConfig\IndividualProductConfig;

class FeaturesManager {
	
	public function __construct() {
		$this->init();
	}
	
	public function init() {
		foreach ( $this->getFeatures() as $feature ) {
			$feature = new $feature();
			
			$feature->run();
		}
	}
	
	public function getFeatures(): array {
		$features = apply_filters( 'custom_price_display/features', array(
			IndividualProductConfig::class,
		) );
		
		return array_filter( $features, function ( $feature ) {
			return class_exists( $feature ) && is_subclass_of( $feature, Feature::class );
		} );
	}
}