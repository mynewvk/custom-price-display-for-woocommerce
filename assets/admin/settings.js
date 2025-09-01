jQuery(document).ready(function ($) {

	const SettingsPage = function () {
		this.prefix = '';
		this.rows = [];

		this.init = function (rows) {
			this.rows = rows;
			this.renderSettings();
		}

		this.getRowById = function (id) {
			return this.rows.find((row) => row.id = id);
		}

		this.renderSettings = function () {
			this.rows.forEach(row => row.shouldBeShown() ? row.show() : row.hide());
		}
	}

	const SettingsRow = function (id, dependencies = {}) {
		this.id = id;
		this.settingsPage = null;
		this.dependencies = dependencies;

		this.init = function (settingsPage) {
			this.settingsPage = settingsPage;
			this.$getRow(false).on('change', () => this.settingsPage.renderSettings());
		}

		this.show = function () {
			this.$getRow().closest('tr').show();
		}

		this.hide = function () {
			this.$getRow().closest('tr').hide();
		}

		this.isChecked = function () {
			return this.$getRow().is(':checked');
		}

		this.isValueEqual = function (value) {
			return this.$getRow().val() === value;
		}

		this.shouldBeShown = function () {

			let pass = true;

			for (const [rowID, value] of Object.entries(this.dependencies)) {
				const row = this.settingsPage.getRowById(rowID);

				if (!row) {
					continue;
				}

				if (value === ':checked') {
					pass = pass && row.isChecked();
					continue;
				}

				if (value === ':unchecked') {
					pass = pass && !row.isChecked();
					continue;
				}

				// there is multiple values to pass
				if (typeof value === 'string') {
					pass = pass && row.isValueEqual(value);
					continue;
				}

				// there is multiple values to pass
				if (value.constructor === Array) {

					let _pass = false;

					value.forEach((_value) => {
						_pass = _pass || row.isValueEqual(_value);
					});

					pass = pass && _pass;
				}
			}

			return pass;
		}

		this.$getRow = function (any = true) {
			let input = $('[name^="' + this.settingsPage.prefix + this.id + '"]');

			if (any && input.is(':radio')) {
				input = input.filter(':checked');
			}

			return input;
		}
	}

	if (typeof window.customPriceDisplaySettingsConditionals === 'undefined') {
		return;
	}

	const settingRows = [];

	Object.entries(window.customPriceDisplaySettingsConditionals).forEach(([key, dependencies]) => {

		if (typeof dependencies !== 'object') {
			dependencies = {};
		}

		if (Array.isArray(dependencies)) {
			dependencies = {};
		}

		settingRows.push(new SettingsRow(key, dependencies));
	});

	const settingsPage = new SettingsPage();

	settingRows.forEach(row => row.init(settingsPage));

	settingsPage.init(settingRows);
});