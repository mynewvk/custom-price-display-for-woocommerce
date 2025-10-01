// Register a TinyMCE plugin
tinymce.PluginManager.add("custom-price-display-custom-mce-buttons", function (editor, url) {

	// Check if global data object exists
	if (!window.custom_price_display_mce_data) {
		return;
	}

	// Loop through all variable buttons defined in the data
	Object.keys(custom_price_display_mce_data.variables).forEach(function (buttonKey) {

		// Only add buttons for the editors listed in the data
		if (!custom_price_display_mce_data.editors.includes(editor.id)) {
			return;
		}

		// Get variable info for this button
		const variableInfo = custom_price_display_mce_data.variables[buttonKey];

		// Add a new button to the TinyMCE toolbar
		editor.addButton(buttonKey, {
			text: variableInfo.name,                 // Button label
			tooltip: variableInfo.description,       // Tooltip text
			icon: false,                             // No icon, text only
			onclick: function () {
				// Insert the variable placeholder into the editor
				editor.insertContent(variableInfo.variableKey);
			}
		});
	});
});