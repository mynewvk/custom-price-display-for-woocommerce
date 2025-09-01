<?php namespace CustomPriceDisplay\Features;

abstract class Feature {
	
	abstract public function getName();
	
	abstract public function getDescription();
	
	abstract public function getSlug();
	
	abstract public function run();
}