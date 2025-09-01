<?php namespace CustomPriceDisplay\Components;

class SwitchOption {
	
	public static function render( $options, $value, $name ): void {
		?>
		<div class="custom-price-display-switch-options">
			<?php foreach ( $options as $optionId => $optionLabel ) : ?>
				<div class="custom-price-display-switch-option">
					<input type="radio" name="<?php echo esc_attr( $name ) ?>"
						   value="<?php echo esc_attr( $optionId ); ?>"
						   id="<?php echo esc_attr( $name . '-' . $optionId ); ?>"
						<?php checked( $value, $optionId ); ?>
						   class="custom-price-display-template-option">

					<label class="custom-price-display-template-label"
						   for="<?php echo esc_attr( $name . '-' . $optionId ); ?>">
						<?php echo esc_html( $optionLabel ); ?>
					</label>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}