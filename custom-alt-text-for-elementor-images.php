<?php
/**
 * Plugin Name: Custom Alt Text for Elementor Images
 * Plugin URI: https://github.com/MIGHTYminnow/custom-alt-text-for-elementor-images
 * Description: Adds options to the Elementor Image Widget, allowing you to set custom alt text, use the attachment alt text, or choose no alt text. This gives you more control over accessibility and SEO for your website's images.
 * Version: 1.0.3
 * Elementor tested up to: 3.16.4
 * Elementor Pro tested up to: 3.16.2
 * Author: MIGHTYminnow
 * Author URI: https://mightyminnow.com
 * Text Domain: catei
 */

class Image_Widget_Custom_Alt {

	public $element;

	public function __construct() {
		add_action( 'elementor/element/image/section_image/before_section_end', [ $this, 'add_controls' ], 10, 2 );
		add_action( 'elementor/frontend/widget/before_render', [ $this, 'before_render' ] );
		add_action( 'elementor/frontend/widget/after_render', [ $this, 'after_render' ] );
	}

	public function add_controls( $element, $args ) {
		$element->add_control(
			'alt_text_type',
			[
				'label' => esc_html__( 'Alternative Text', 'catei' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'attachment',
				'options' => [
					'attachment'  => esc_html__( 'Attachment Alt Text', 'catei' ),
					'none' => esc_html__( 'None', 'catei' ),
					'custom' => esc_html__( 'Custom', 'catei' ),
				],
			]
		);

		$element->add_control(
			'custom_alt_text',
			[
				'label' => esc_html__( 'Custom Alt Text', 'catei' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true
				],
				'condition' => [
					'alt_text_type' => 'custom',
				],
			]
		);
	}

	public function after_render( $element ) {
		if ( 'image' === $element->get_name() ) {
			remove_filter( 'elementor/image_size/get_attachment_image_html', [ $this, 'replace_alt_text' ] );
		}
	}

	public function before_render( $element ) {
		if ( 'image' === $element->get_name() ) {
			$this->element = $element;
			add_filter( 'elementor/image_size/get_attachment_image_html', [ $this, 'replace_alt_text' ], 10, 4 );
		}
	}

	public function replace_alt_text( $html, $settings, $image_size_key, $image_key ) {
		$image = $settings[ $image_key ];
		$alt = get_post_meta( $settings['image']['id'], '_wp_attachment_image_alt', true );

		switch ( $this->element->get_settings( 'alt_text_type' ) ) {
			case 'custom' :
				return str_replace(
					'alt="' . esc_attr( $alt ) . '"',
					'alt="' . esc_attr( $this->element->get_settings_for_display( 'custom_alt_text' ) ) . '"',
					$html
				);
				break;
			case 'none':
				return str_replace(
					'alt="' . esc_attr( $alt ) . '"',
					'alt=""',
					$html
				);
				break;
			default:
				return $html;
		}
	}

}

global $image_widget_custom_alt;
$image_widget_custom_alt = new Image_Widget_Custom_Alt();
