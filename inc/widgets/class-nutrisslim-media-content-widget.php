<?php
use Elementor\Widget_Base;

class Nutrisslim_ACF_Media_Content_Widget extends Widget_Base {

    public function get_name() {
        return 'nutrisslim_acf_media_content';
    }

    public function get_title() {
        return __( 'ACF Media Content', 'nutrisslim-elementor-widgets' );
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return [ 'nutrisslim' ];
    }

    protected function _register_controls() {
        // No controls needed as the data comes from ACF directly.
    }

    protected function render() {
        if (!function_exists('get_field')) {
            echo 'ACF is not active.';
            return;
        }

        $default_field = 'media_content';
        $user_country = isset($_COOKIE['user_country']) ? sanitize_text_field($_COOKIE['user_country']) : '';
        $allowed_countries = ['DE', 'FR', 'IT'];

        $current_acf_field = in_array($user_country, $allowed_countries)
            ? 'media_content_' . strtolower($user_country)
            : $default_field;

        $media_contents = get_field($current_acf_field);
        if (!$media_contents) return;

        foreach ($media_contents as $media_content) {
            $image_id = $media_content['image'];
            $description = $media_content['description'];
            $style = isset($media_content['style']) ? esc_attr($media_content['style']) : '';

            if (!empty($media_content['title'])) {
                echo '<div class="subsection media-content-subsection-single ' . $style . '">';
                echo '<div class="content-holder">' . esc_html($media_content['title']) . '</div>';
                echo '</div>';
            }

            if ($image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'large');
                echo '<div class="subsection media-content-subsection ' . $style . '">';
                if ($image_url) {
                    echo '<div class="img-holder"><img src="' . esc_url($image_url) . '" alt="' . esc_attr(get_the_title()) . '" style="max-width:100%; height:auto;"></div>';
                }
                if ($description) {
                    echo '<div class="content-holder">' . $description . '</div>';
                }
                echo '</div>';
            } else if ($description) {
                echo '<div class="subsection media-content-subsection-single ' . $style . '">';
                echo '<div class="content-holder">' . $description . '</div>';
                echo '</div>';
            }

            // ========== Option with Icons ==========
            if (!empty($media_content['option_with_icons'])) {
                echo '<div class="option-with-icons-wrapper">';
                foreach ($media_content['option_with_icons'] as $option) {
                    $layout = $option['vrstni_red'] ?? 'slika-levo';
                    $main_slika_url = $option['main_slika'] ?? '';

                    echo '<div class="option-row layout-' . esc_attr($layout) . '">';

                    if ($layout === 'slika-levo') {
                        // IMAGE LEFT
                        if ($main_slika_url) {
                            echo '<div class="option-main-img"><img src="' . esc_url($main_slika_url) . '" alt="" style="max-width:100%; height:auto;"></div>';
                        }
                    }

                    // ICONS + TEXT
                    echo '<div class="option-content">';
                    if (!empty($option['ikone_in_besedilo'])) {
                        echo '<div class="icons-text-group">';
                        foreach ($option['ikone_in_besedilo'] as $item) {
                            $icon_url = $item['ikona'];
                            $besedilo = $item['besedilo'];

                            echo '<div class="icon-text-item">';
                            if ($icon_url) {
                                echo '<div class="icon ttttt"><img src="' . $icon_url . '" alt="" style="width:50px; height:auto;"></div>';
                            }
                            if ($besedilo) {
                                echo '<div class="text">' . $besedilo . '</div>';
                            }
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    echo '</div>'; // .option-content

                    if ($layout === 'slika-desno' && $main_slika_url) {
                        echo '<div class="option-main-img"><img src="' . esc_url($main_slika_url) . '" alt="" style="max-width:100%; height:auto;"></div>';
                    }

                    echo '</div>'; // .option-row
                }
                echo '</div>'; // .option-with-icons-wrapper
            }

            // ========== Banner ==========
            if (!empty($media_content['enable_banner']) && in_array('Enable', $media_content['enable_banner'])) {
                $banner_image = $media_content['banner_image'];
                $banner_text = $media_content['banner'];
                $banner_color = $media_content['banner_color'] ?: '#000';

                $banner_image_url = is_numeric($banner_image)
                    ? wp_get_attachment_image_url($banner_image, 'large')
                    : $banner_image;

                if ($banner_image_url) {
                    echo '<div class="nutrisslim-banner" style="position: relative; background-image: url(\'' . esc_url($banner_image_url) . '\'); background-size: cover; background-position: center; min-height: 500px;">';
                    if (!empty($banner_text)) {
                        echo '<div class="nutrisslim-banner-text" style="position: absolute; bottom: 0; left: 0; right: 0; background-color: ' . esc_attr($banner_color) . '; color: #fff; text-align: center; padding: 20px; font-weight: bold;">';
                        echo wp_kses_post($banner_text);
                        echo '</div>';
                    }
                    echo '</div>';
                }
            }
        }
    }
}
