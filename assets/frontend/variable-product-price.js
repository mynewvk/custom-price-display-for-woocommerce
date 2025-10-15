jQuery(document).ready(function ($) {

	const PriceUpdater = function () {

		this.defaultHTMLPrices = {};

		this.init = function () {
			$(document).on('found_variation', (event, variation) => {

				var $form = jQuery(event.target).closest('.variations_form');
				var parentProductId = $form.find('input[name="product_id"]').val();

				if (this.defaultHTMLPrices[parentProductId] === undefined) {

					let priceContainer = this.$getPriceContainer(parentProductId);

					if (priceContainer.length) {
						this.defaultHTMLPrices[parentProductId] = priceContainer.html();
					}
				}

				if (variation.price_html) {
					this.updatePrice(parentProductId, variation.price_html);
				} else {
					this.updatePrice(parentProductId, this.defaultHTMLPrices[parentProductId]);
				}
			});

			$(document).on('reset_data', (event) => {
				const $form = jQuery(event.target).closest('.variations_form');
				const parentProductId = $form.find('input[name="product_id"]').val();

				if (this.defaultHTMLPrices[parentProductId] !== undefined) {
					this.updatePrice(parentProductId, this.defaultHTMLPrices[parentProductId]);
				}
			});
		}

		this.$getPriceContainer = function (productId) {
			return $('.cpdfw-variable-product-price[data-product-id="' + productId + '"]');
		};

		this.updatePrice = function (productId, priceHTML) {

			const priceContainer = this.$getPriceContainer(productId);

			priceContainer.length && priceContainer.html(priceHTML);
		}
	};

	const priceUpdater = new PriceUpdater();
	priceUpdater.init();
});
