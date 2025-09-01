(function () {
	tinymce.PluginManager.add('custom-price-display-custom-mce-buttons', function (editor, url) {

		if (customPriceDisplayMCEAvailableVariables === undefined || customPriceDisplayMCEEditors === undefined) {
			return;
		}

		Object.keys(customPriceDisplayMCEAvailableVariables).forEach(function (buttonKey) {

			if (!customPriceDisplayMCEEditors.includes(editor.id)) {
				return;
			}

			editor.addButton(buttonKey, {
				text: customPriceDisplayMCEAvailableVariables[buttonKey].name,
				tooltip: customPriceDisplayMCEAvailableVariables[buttonKey].description,
				icon: false,
				onclick: function () {
					editor.insertContent(customPriceDisplayMCEAvailableVariables[buttonKey].variableKey);
				}
			});
		});
	});
})();
