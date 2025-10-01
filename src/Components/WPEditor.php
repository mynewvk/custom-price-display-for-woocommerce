<?php namespace CustomPriceDisplay\Components;

use CustomPriceDisplay\Core\ServiceContainerTrait;
use CustomPriceDisplay\CustomPriceDisplayPlugin;

class WPEditor {
	
	use ServiceContainerTrait;
	
	protected static ?self $instance = null;
	
	protected array $editors = array();
	
	protected function __construct() {
		
		add_action( 'admin_footer', function () {
			
			wp_localize_script( 'custom-price-display__mce-editor-localized', 'custom_price_display_mce_data', array(
				'variables' => $this->getAvailableVariables(),
				'editors'   => $this->editors,
			) );
			
			wp_enqueue_script( 'custom-price-display__mce-editor-localized' );
		} );
		
		add_filter( 'mce_buttons', function ( $buttons, $editorId ) {
			
			if ( ! in_array( $editorId, $this->editors ) ) {
				return $buttons;
			}
			
			return array_merge( $buttons, array_keys( $this->getAvailableVariables() ) );
		}, 10, 2 );
		
		add_filter( 'mce_external_plugins', function ( $plugins ) {
			
			// Empty script to include custom variables for the mce.js script
			wp_register_script( 'custom-price-display__mce-editor-localized', '', array(),
				CustomPriceDisplayPlugin::VERSION, true );
			
			$plugins['custom-price-display-custom-mce-buttons'] = $this->getContainer()->getFileManager()->locateJSAsset( 'admin/mce' );
			
			return $plugins;
			
		}, 9999 );
	}
	
	public static function instance(): self {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function render( $id, $content, $placeholders, array $settings = array() ): void {
		$this->editors[] = $id;
		
		$editorCSS = "#wp-{$id}-wrap { max-width: 800px; }";
		
		$settings = wp_parse_args( $settings, array(
			'wpautop'       => true,
			'media_buttons' => false,
			
			'textarea_name'    => $id,
			'editor_height'    => 30,
			'tabindex'         => null,
			'editor_class'     => 'cpdfw-message-template-mce',
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
			//'editor_css'       => '<style>' . $editorCSS . '</style>',
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
			'cpdfw_lowest_price'  => array(
				'name'        => __( 'Lowest Price', 'custom-price-display-for-woocommerce' ),
				'description' => __( '{cpdfw_lowest_price} - lowest price of the variable product.',
					'custom-price-display-for-woocommerce' ),
				'variableKey' => '{cpdfw_lowest_price}',
			),
			'cpdfw_highest_price' => array(
				'name'        => __( 'Highest Price', 'custom-price-display-for-woocommerce' ),
				'description' => __( '{cpdfw_highest_price} - highest price of the variable product.',
					'custom-price-display-for-woocommerce' ),
				'variableKey' => '{cpdfw_highest_price}',
			),
		);
	}
}