<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Nutrisslim_Features_Image_Widget extends Widget_Base {

    public function get_name() {
        return 'nutrisslim_features_image';
    }

    public function get_title() {
        return __( 'Nutrisslim Features Image', 'nutrisslim-elementor-widgets' );
    }

    public function get_icon() {
        return 'eicon-image-box';
    }

    public function get_categories() {
        return [ 'nutrisslim' ];
    }

    protected function _register_controls() {
        // Optionally, add controls for the widget here.
    }

    protected function render() {

        // Determine the user's country based on the cookie.
        // Default to 'de' if no cookie is set.
        $country = 'de';
        if ( isset($_COOKIE['user_country']) && !empty($_COOKIE['user_country']) ) {
            // Convert the cookie value to lowercase to match our field suffixes.
            $country = strtolower(sanitize_text_field($_COOKIE['user_country']));
        }

        // Build the field names based on the country.
        $featured_image_field    = 'featured_image_' . $country;
        $feature_top_left_field  = 'featuretopleft_' . $country;
        $feature_top_right_field = 'featuretopright_' . $country;
        $feature_bottom_left_field  = 'featurebottomleft_' . $country;
        $feature_bottom_right_field = 'featurebottomright_' . $country;

        // Get ACF fields based on the dynamically built field names.
        $featured_image_id = get_field($featured_image_field);
        $feature_top_left  = get_field($feature_top_left_field);
        $feature_top_right = get_field($feature_top_right_field);
        $feature_bottom_left  = get_field($feature_bottom_left_field);
        $feature_bottom_right = get_field($feature_bottom_right_field);
        
        if (!$featured_image_id) {
            return;
        }
        ?>
        <style>
        .nutrisslim-features-image {
            position: relative;
            background-size: cover;
            background-position: center;
            aspect-ratio: 425 / 152;
            min-height: 494px;
        }
        
        .nutrisslim-features-image div.feature {
            display: inline-block;
            font-weight: bold;
            text-align: center;
            font-size: 25px;
            line-height: 30px;
            background-color: #fff;
            height: 200px;
            width: 200px;
            border-radius: 200px;
            justify-content: center;
            display: flex;
            align-content: center;
            flex-wrap: wrap;
            color: #ef6d89;
        }
        
        .feature-bottom-right {
            margin-bottom: 2rem;
            margin-right: 4rem;
        }
        
        .feature-bottom-left {
            margin-bottom: 2rem;
            margin-left: 4rem;
        }
        
        .feature-top-right {
            margin-top: 2rem;
            margin-right: 4rem;
        }
        
        .feature-top-left {
            margin-top: 2rem;
            margin-left: 4rem;
        }
        
        @media (max-width: 1400px) {
            .nutrisslim-features-image {
                aspect-ratio: auto;
            }
        }
        
        @media (max-width: 767px) {
            .nutrisslim-features-image div.feature {
                font-size: 16px;
                height: 135px;
                width: 135px;
                line-height: 21px;
            }
            .feature-bottom-right {
                margin-bottom: 0.5rem;
                margin-right: 0.5rem;
            }
            .feature-bottom-left {
                margin-bottom: 0.5rem;
                margin-left: 0.5rem;
            }
            .feature-top-right {
                margin-top: 0.5rem;
                margin-right: 0.5rem;
            }
            .feature-top-left {
                margin-top: 0.5rem;
                margin-left: 0.5rem;
            }
        }
        </style>
        <?php

        $featured_image_url = wp_get_attachment_image_url($featured_image_id, 'full');

        if (!$featured_image_url) {
            echo 'No featured image set.';
            return;
        }

        // Output the structure with inline styles.
        echo '<div class="nutrisslim-features-image" style="position: relative; background-image: url(\'' . esc_url($featured_image_url) . '\'); background-size: cover; background-position: center;">';
        echo '<div class="feature feature-top-left" style="position: absolute; top: 0; left: 0;">' . esc_html($feature_top_left) . '</div>';
        echo '<div class="feature feature-top-right" style="position: absolute; top: 0; right: 0;">' . esc_html($feature_top_right) . '</div>';
        echo '<div class="feature feature-bottom-left" style="position: absolute; bottom: 0; left: 0;">' . esc_html($feature_bottom_left) . '</div>';
        echo '<div class="feature feature-bottom-right" style="position: absolute; bottom: 0; right: 0;">' . esc_html($feature_bottom_right) . '</div>';
        
        // Your additional product price logic remains unchanged.
        global $product;
        $price = $product->get_price();
        $consumption_period = get_field('consumption_period');

        // Get price including tax.
        $price_with_tax = wc_get_price_including_tax($product);
        $price_with_tax = floatval($price_with_tax);
        $consumption_period = floatval($consumption_period);

        if ($consumption_period != 0) {
            $dailyprice = wc_price($price_with_tax / $consumption_period);
        } else {
            $dailyprice = wc_price(0);
        }

        echo '</div>';

        $user_country = isset($_COOKIE['user_country']) ? sanitize_text_field($_COOKIE['user_country']) : 'DE';

$daily_texts = [
    'DE' => 'Die Einnahme von %1$s kostet Sie nur <br /><span class="daily">%2$s pro Tag!</span>',
    'IT' => 'Usando %1$s - ti costerà solo <br /><span class="daily">%2$s al giorno!</span>',
    'FR' => 'Utiliser %1$s - vous coûtera seulement <br /><span class="daily">%2$s par jour !</span>',
    'DEFAULT' => 'Using %1$s - will cost you only <br /><span class="daily">%2$s per day!</span>'
];

$translated_daily = $daily_texts[$user_country] ?? $daily_texts['DEFAULT'];

echo '<div class="dailyprice primary-bg-color">';
echo '<div class="container">';
// Country-specific product title translation
$title_meta_keys = [
    'DE' => '_product_title_de',
    'IT' => '_product_title_it',
    'FR' => '_product_title_fr',
];

$product_id = get_the_ID();
$translated_title = isset($title_meta_keys[$user_country])
    ? get_post_meta($product_id, $title_meta_keys[$user_country], true)
    : '';

$translated_title = !empty($translated_title) ? $translated_title : get_the_title();

echo '<p>' . sprintf(
    $translated_daily,
    esc_html($translated_title),
    $dailyprice
) . '</p>';
echo '</div>';
echo '</div>';

    }

    protected function _content_template() {
        // JS content template for frontend rendering if needed.
    }
}
