<?php

if ( ! class_exists( 'WP_Customize_Control' ) ) {
	return null;
}

/**
 * Multiselect option for WP Customizer
 *
 * @param $wp_customize
 */

/**
 * Multiple select customize control class.
 */
class Customize_Control_Multiple_Select extends WP_Customize_Control {

    /**
     * The type of customize control being rendered.
     */
    public $type = 'multiple-select';

    /**
     * Displays the multiple select on the customize screen.
     */
    public function render_content() {

        if ( empty( $this->choices ) ) {
            return;
        }
        ?>
        
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
            <select <?php $this->link(); ?> multiple="multiple" style="height: 100%; min-height:200px">
                <?php
                foreach ( $this->choices as $value => $label ) {
                    $selected = ( in_array( $value, $this->value() ) ) ? selected( 1, 1, false ) : '';
                    echo '<option value="' . esc_attr( $value ) . '"' . $selected . '>' . $label . '</option>';
                }
                ?>
            </select>
        </label>
    <?php }
}


function wpbm_pages() {
    $page_arr = array();
    $page_arr[0] = 'None';
    foreach ( get_pages() as $pages => $page ) {
        $page_arr[ $page->ID ] = $page->post_title;
    }

    return $page_arr;
}

/**
 * Validate the options against the existing pages
 *
 * @param  string[] $input
 *
 * @return string
 */
function wpbm_pages_sanitize( $input ) {
    $valid = wpbm_pages();

    foreach ( $input as $value ) {
        if ( ! array_key_exists( $value, $valid ) ) {
            return [];
        }
    }

    return $input;
}