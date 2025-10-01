<?php namespace CustomPriceDisplay\Settings\CustomOptions;

use CustomPriceDisplay\Components\WPEditor;
use CustomPriceDisplay\Core\ServiceContainerTrait;

class RichText {

	const FIELD_TYPE = 'custom_price_display_rich_text';

	use ServiceContainerTrait;

	public function __construct() {

		add_action( 'woocommerce_admin_field_' . self::FIELD_TYPE, array( $this, 'render' ) );

		add_action( 'woocommerce_admin_settings_sanitize_option', function ( $value, $option, $rawValue ) {

			if ( self::FIELD_TYPE === $option['type'] ) {
				return wp_kses_post( $rawValue );
			}

			return $value;
		}, 10, 3 );
	}

	public function render( $value ) {
		if ( ! isset( $value['id'] ) ) {
			$value['id'] = '';
		}

		if ( ! isset( $value['title'] ) ) {
			$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
		}

		if ( ! isset( $value['default'] ) ) {
			$value['default'] = '';
		}
		if ( ! isset( $value['desc'] ) ) {
			$value['desc'] = '';
		}

		if ( ! isset( $value['value'] ) ) {
			$value['value'] = \WC_Admin_Settings::get_option( $value['id'], $value['default'] );
		}

		$option_value = $value['value'];

		$value['placeholders'] = is_array( $value['placeholders'] ) ? $value['placeholders'] : array();
		?>

		<tr valign="top">

			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>

			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
				<?php WPEditor::instance()->render( $value['id'], $option_value, $value['placeholders'] ); ?>

				<?php if ( $value['desc'] ) : ?>
					<p class="description">
						<?php
							// audit.php.wp.security.xss.shortcode-attr ignore
							echo wp_kses_post( $value['desc'] );
						?>
					</p>
					<?php if ( ! cpdfw_fs()->can_use_premium_code() ): ?>
						<p style="color: red">
							<?php esc_html_e( 'This feature is available only in the premium version.',
									'custom-price-display-for-woocommerce' ); ?>
							<a href="<?php echo esc_html( cpdfw_fs_activation_url() ) ?>">
								<?php esc_html_e( 'Upgrade your plan', 'custom-price-display-for-woocommerce' ); ?></a>
						</p>
					<?php endif; ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}
}
