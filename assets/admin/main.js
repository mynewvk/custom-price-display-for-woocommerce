jQuery(document).ready(function ($) {
	/**
	 * Generic function to handle visibility logic
	 */
	function refreshVisibility(priceFormat, prefixWrapper, suffixWrapper, templateWrapper) {
		const selectedFormat = priceFormat.filter(":checked").val();

		switch (selectedFormat) {
			case "custom":
				templateWrapper.show();
				prefixWrapper.hide();
				suffixWrapper.hide();
				break;
			case "default":
			case "":
				templateWrapper.hide();
				prefixWrapper.hide();
				suffixWrapper.hide();
				break;
			default:
				templateWrapper.hide();
				prefixWrapper.show();
				suffixWrapper.show();
		}
	}

	/**
	 * Initialize product type visibility handling
	 */
	function initProductVisibility(type) {
		const prefix = type.toUpperCase();

		const priceFormat = $(`[name=cpdfw_${type}_product_custom_price_format]`);
		const prefixWrapper = $(`#cpdfw_${type}_product_price_prefix`).closest(".custom-price-display-product-option");
		const suffixWrapper = $(`#cpdfw_${type}_product_price_suffix`).closest(".custom-price-display-product-option");
		const templateWrapper = $(`#cpdfw_${type}_product_custom_price_template`).closest(".custom-price-display-product-option");

		if (!priceFormat.length) return;

		const refresh = () => refreshVisibility(priceFormat, prefixWrapper, suffixWrapper, templateWrapper);

		priceFormat.on("change", refresh);
		refresh(); // Run once on load
	}

	// Initialize for both product types
	["simple", "variable"].forEach(initProductVisibility);
});