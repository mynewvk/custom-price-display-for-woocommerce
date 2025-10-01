<?php namespace CustomPriceDisplay\Settings\CustomOptions;

use WC_Admin_Settings;

class MultipleTextInputs {
	
	const FIELD_TYPE = 'custom_price_display_multiple_text_input_option';
	
	public function __construct() {
		add_action( 'woocommerce_admin_field_' . self::FIELD_TYPE, array( $this, 'render' ) );
		
		add_action( 'woocommerce_admin_settings_sanitize_option', function ( $value, $option, $rawValue ) {
			
			if ( self::FIELD_TYPE === $option['type'] ) {
				$value = is_array( $value ) ? $value : array();
				
				$value = array_map( 'sanitize_text_field', $value );
				$value = array_filter( $value );
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
			$value['default'] = array();
		}
		
		if ( ! isset( $value['value'] ) ) {
			$value['value'] = WC_Admin_Settings::get_option( $value['id'], $value['default'] );
		}
		
		if ( ! isset( $value['desc'] ) ) {
			$value['desc'] = '';
		}
		
		$value['value'] = isset( $value['value'] ) ? (array) $value['value'] : array();
		$value['value'] = array_map( 'sanitize_text_field', $value['value'] );
		
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
				<div>

					<div style="display: flex; width: 400px; justify-content: space-between">
						
						<?php foreach ( $value['options'] as $optionKey => $option ): ?>
							<?php
							$optionValue = isset( $value['value'][ $optionKey ] ) ? $value['value'][ $optionKey ] : '';
							?>
							<div>
								<label for="<?php echo esc_attr( $value['id'] ); ?>-singular">
									<?php echo esc_html( $option['label'] ); ?>:
								</label>
								<br>
								<input type="text" style="width: 190px;"
									   value="<?php echo esc_attr( $optionValue ); ?>"
									   name="<?php echo esc_attr( $value['id'] ); ?>[<?php echo esc_attr( $optionKey ); ?>]"
									   id="<?php echo esc_attr( $value['id'] ); ?>-<?php echo esc_attr( $optionKey ); ?>"
									   placeholder="<?php echo esc_attr( $option['placeholder'] ); ?>">
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<p class="description">
					<?php
						echo wp_kses_post( $value['desc'] ); // audit.php.wp.security.xss.shortcode-attr ignore
					?>
				</p>
			</td>
		</tr>
		<?php
	}
}