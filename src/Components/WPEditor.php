<?php namespace CustomPriceDisplay\Components;

use CustomPriceDisplay\Core\ServiceContainerTrait;

class WPEditor {
	
	use ServiceContainerTrait;
	
	protected static $instance = null;
	
	protected $editors = array();
	
	protected function __construct() {
		add_action( 'admin_footer', function () {
			?>
			<script>
				const customPriceDisplayMCEAvailableVariables = JSON.parse('<?php echo wp_kses_post( wp_json_encode( $this->getAvailableVariables() ) ); ?>');
				const customPriceDisplayMCEEditors = JSON.parse('<?php echo wp_kses_post( wp_json_encode( $this->editors ) )?>');
			</script>
			<?php
		} );
		
		add_filter( 'mce_buttons', function ( $buttons, $editorId ) {
			
			if ( ! in_array( $editorId, $this->editors ) ) {
				return $buttons;
			}
			
			return array_merge( $buttons, array_keys( $this->getAvailableVariables() ) );
		}, 10, 2 );
		
		add_filter( 'mce_external_plugins', function ( $plugins, $editor ) {

			$plugins['custom-price-display-custom-mce-buttons'] = $this->getContainer()->getFileManager()->locateJSAsset( 'admin/mce' );
			
			return $plugins;
		}, 9999, 2 );
	}
	
	public static function instance(): self {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function render( $id, $content, $placeholders, array $settings = array() ): void {
		?>
		<style>
			#wp-<?php echo esc_html($id); ?>-editor-tools {
				display: none !important;
			}
		</style>
		<?php
		
		$this->editors[] = $id;
		
		$settings = wp_parse_args( $settings, array(
			'wpautop'       => true,
			'media_buttons' => false,
			
			'textarea_name'    => $id,
			'editor_height'    => 30,
			'tabindex'         => null,
			'editor_class'     => 'cpd-message-template-mce',
			'tinymce'          => array(
				'resize'   => 'vertical',
				'menubar'  => false,
				'wpautop'  => true,
				'toolbar2' => '',
				'toolbar1' => implode( ',', array_merge( array(
					'bold',
					'italic',
					'strikethrough',
					'link',
					'forecolor',
					'backcolor',
					'spellchecker',
				), $placeholders ) ),
			),
			'quicktags'        => array(
				'id'      => $id,
				'buttons' => 'strong,em,del',
			),
			'drag_drop_upload' => false,
		) );
		
		wp_editor( $content, $id, $settings );
	}
	
	protected function getAvailableVariables(): array {
		return array(
			'cpd_lowest_price'  => array(
				'name'        => __( 'Lowest Price', 'custom-price-display-for-woocommerce' ),
				'description' => __( '{cpd_lowest_price} - lowest price of the variable product.',
					'custom-price-display-for-woocommerce' ),
				'variableKey' => '{cpd_lowest_price}',
			),
			'cpd_highest_price' => array(
				'name'        => __( 'Highest Price', 'custom-price-display-for-woocommerce' ),
				'description' => __( '{cpd_highest_price} - highest price of the variable product.',
					'custom-price-display-for-woocommerce' ),
				'variableKey' => '{cpd_highest_price}',
			),
		);
	}
}