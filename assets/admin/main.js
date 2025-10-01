jQuery(document).ready(function () {
	// Cache DOM elements for performance and readability
	const $priceFormat   = jQuery(".custom-price-display-product-tab .custom-price-display-template-option");
	const $pricePrefix   = jQuery("#cpdfw_price_prefix").closest(".custom-price-display-product-option");
	const $priceSuffix   = jQuery("#cpdfw_price_suffix").closest(".custom-price-display-product-option");
	const $priceTemplate = jQuery("#cpdfw_custom_price_template").closest(".custom-price-display-product-option");

	/**
	 * Show or hide price options depending on the selected format.
	 */
	function refreshVisibility() {
		const selectedFormat = $priceFormat.filter(":checked").val();

		if (selectedFormat === "custom") {
			// Show only the custom template option
			$priceTemplate.show();
			$pricePrefix.hide();
			$priceSuffix.hide();
		}
		else if (selectedFormat === "default" || selectedFormat === "") {
			// Hide all options
			$priceTemplate.hide();
			$pricePrefix.hide();
			$priceSuffix.hide();
		}
		else {
			// Show prefix & suffix options, hide template
			$priceTemplate.hide();
			$pricePrefix.show();
			$priceSuffix.show();
		}
	}

	// If format options exist, bind event and refresh visibility on load
	if ($priceFormat.length > 0) {
		$priceFormat.on("change", refreshVisibility);
		refreshVisibility(); // Run once on page load
	}
});
