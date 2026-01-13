<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Nutrisslim_FAQ_Widget extends Widget_Base {

    public function get_name() {
        return 'nutrisslim_faq';
    }

    public function get_title() {
        return __( 'Nutrisslim FAQ', 'nutrisslim-elementor-widgets' );
    }

    public function get_icon() {
        return 'eicon-help-o';
    }

    public function get_categories() {
        return [ 'nutrisslim', 'nutrisslim-landing' ];
    }

    public function get_html_wrapper_class() {
        return parent::get_html_wrapper_class() . ' vprasanja';
    }

    protected function _register_controls() {
        // Optionally, add controls for the widget here.
    }

    protected function render() {
        global $product;

        // If not on a product page, attempt to load the "selected_product" via ACF
        if ( ! $product ) {
            $pid = get_field('selected_product');
            if (!empty($pid)) {
                $pid = $pid[0];
                $product = wc_get_product($pid);
            }
        }

        // Ensure we're on a product page and ACF is active
        if ( ! $product || ! function_exists('get_field') ) {
            echo 'This widget is only available on product pages with ACF enabled.';
            return;
        }

        // Get user country from the cookie, default to DE if not set
        $user_country = isset($_COOKIE['user_country']) ? strtoupper(sanitize_text_field($_COOKIE['user_country'])) : 'DE';

        // Build the field key based on the country: "faq_de", "faq_it", or "faq_fr"
        $faq_field_key = 'faq_' . strtolower($user_country);

        // Fetch FAQ items from the product based on the user’s country
        $product_faq_items = get_field($faq_field_key, $product->get_id());

        // Fetch FAQ items from the ACF “Options” page (if you also store some global FAQ there)
        $options_faq_items = get_field($faq_field_key, 'options');

        // Merge the two arrays (with a limit of 4 from the product-specific FAQ)
        $faq_items = [];
        if (is_array($product_faq_items)) {
            $faq_items = array_slice($product_faq_items, 0, 4);
        }
        if (is_array($options_faq_items)) {
            $faq_items = array_merge($faq_items, $options_faq_items);
        }
        
        // If no FAQ items found, bail out
        if (empty($faq_items)) {
            echo 'No FAQ items found.';
            return;
        }

        // Render
        echo '<h2>' . esc_html__( 'Frequently Asked Questions of Our Users', 'nutrisslim-suiteV2' ) . '</h2>';
        echo '<div class="nutrisslim-faq">';
        echo '<div class="swiper faqGrid">';
        echo '<div class="swiper-wrapper">';

        foreach ($faq_items as $item) {
            // Each $item is one row in the repeater, expecting subfields named: icon, question, answer
            $icon_id  = isset($item['icon']) ? $item['icon'] : '';
            $question = isset($item['question']) ? $item['question'] : '';
            $answer   = isset($item['answer']) ? $item['answer'] : '';

            echo '<div class="swiper-slide faq-item">';

            // If there's an icon (attachment ID), display it
            if ($icon_id) {
                $icon_url = wp_get_attachment_image_url($icon_id, 'full');
                if ($icon_url) {
                    echo '<img src="' . esc_url($icon_url) . '" alt="FAQ Icon">';
                }
            }

            echo '<h3 class="faq-question">' . esc_html($question) . '</h3>';
            echo '<div class="faq-answer">' . wp_kses_post($answer) . '</div>';
            echo '</div>'; // .swiper-slide
        }

        echo '</div>'; // .swiper-wrapper
        echo '</div>'; // .swiper
        echo '</div>'; // .nutrisslim-faq
    }
}
