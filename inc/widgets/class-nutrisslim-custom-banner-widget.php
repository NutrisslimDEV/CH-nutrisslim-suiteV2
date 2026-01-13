<?php
use Elementor\Widget_Base;

class Nutrisslim_Custom_Banner_Widget extends Widget_Base {

    public function get_name() {
        return 'nutrisslim_custom_banner';
    }

    public function get_title() {
        return __( 'Custom ACF Banner', 'nutrisslim-elementor-widgets' );
    }

    public function get_icon() {
        return 'eicon-banner';
    }

    public function get_categories() {
        return [ 'nutrisslim' ];
    }

    protected function _register_controls() {
        // No Elementor controls needed — all data comes from ACF
    }

    protected function render() {
        if (!function_exists('get_field')) {
            echo 'ACF is not active.';
            return;
        }

        // Determine country suffix
        $user_country = isset($_COOKIE['user_country']) ? sanitize_text_field($_COOKIE['user_country']) : '';
        $allowed_countries = ['DE', 'FR', 'IT'];
        $suffix = in_array($user_country, $allowed_countries) ? '_' . strtolower($user_country) : '';

        // ACF field names based on country
        $banner_image_field = 'banner_image' . $suffix;
        $banner_text_field = 'banner_text' . $suffix;
        $banner_color_field = 'banner__color' . $suffix;

        // Get ACF fields
        $banner_image = get_field($banner_image_field);
        $banner_text = get_field($banner_text_field);
        $banner_color = get_field($banner_color_field);

        // Get image URL (handle both ID and URL return formats)
        $banner_image_url = '';
        if ($banner_image) {
            $banner_image_url = is_numeric($banner_image)
                ? wp_get_attachment_image_url($banner_image, 'large')
                : $banner_image;
        }

        // Output banner
        if ($banner_image_url || $banner_text) {
                    echo '<div class="nutrisslim-custom-banner" style="position: relative; background-image: url(\'' . esc_url($banner_image_url) . '\'); background-size: cover; background-position: center; min-height: 500px;">';
                    echo '</div>';
                    if (!empty($banner_text)) {
                                    echo '<div class="nutrisslim-custom-banner-text" style="position: relative; bottom: 0; left: 0; right: 0; background-color: ' . esc_attr($banner_color) . '; color: #fff; text-align: center; padding: 20px; font-weight: bold;">';
                                    echo wp_kses_post($banner_text);
                                    echo '</div>';
                                }
                }
    }
}
