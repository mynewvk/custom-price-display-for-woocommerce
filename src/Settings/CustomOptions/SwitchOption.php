<?php namespace CustomPriceDisplay\Settings\CustomOptions;

use CustomPriceDisplay\Components\SwitchOption as SwitchOptionGlobal;
use WC_Admin_Settings;

class SwitchOption {
	
	const FIELD_TYPE = 'custom_price_display_switch_option';
	
	public function __construct() {
		add_action( 'woocommerce_admin_field_' . self::FIELD_TYPE, array( $this, 'render' ) );
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
			$value['value'] = WC_Admin_Settings::get_option( $value['id'], $value['default'] );
		}
		
		$option_value = $value['value'];
		
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
				<fieldset>
					<?php SwitchOptionGlobal::render( $value['options'], $option_value, $value['id'] ); ?>

					<p class="description">
						<?php echo esc_html( $value['desc'] ); ?>
					</p>
					
					<?php if ( isset( $value['extended_description'] ) ) : ?>
						<div class="custom-price-display-extended-description">
							<?php
								echo wp_kses_post( $value['extended_description'] ); // audit.php.wp.security.xss.shortcode-attr ignore
							?>
						</div>
					<?php endif; ?>
					
				</fieldset>
			</td>
		</tr>
		<?php
	}
}