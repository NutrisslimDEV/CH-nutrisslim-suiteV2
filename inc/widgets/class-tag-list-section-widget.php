<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Tag_List_Section_Widget extends Widget_Base {

    public function get_name() {
        return 'tag_list_section';
    }

    public function get_title() {
        return __( 'Tag List Section', 'text-domain' );
    }

    public function get_icon() {
        return 'eicon-tags';
    }

    public function get_categories() {
        return [ 'nutrisslim' ];
    }

    protected function _register_controls() {
        // Controls can be registered here if needed.
    }

    protected function render() {
        $product_id = get_the_ID();
        $user_country = isset($_COOKIE['user_country']) ? sanitize_text_field($_COOKIE['user_country']) : 'DE';
    
        // ACF field keys by country
        $field_key_map = [
            'DE' => 'tag_de',
            'IT' => 'tag_it',
            'FR' => 'tag_fr',
        ];
    
        $acf_field_key = $field_key_map[$user_country] ?? 'tag_de';
    
        // Get tags
        $terms = wp_get_post_terms($product_id, 'product_tag', ['hide_empty' => false]);
    
        if (!is_wp_error($terms) && !empty($terms)) {
            $translated_tags = [];
    
            foreach ($terms as $term) {
                // Get ACF field from tag (term) object
                $translated = get_field($acf_field_key, 'product_tag_' . $term->term_id);
    
                if ($translated) {
                    $translated_tags[] = esc_html($translated);
                } else {
                    $translated_tags[] = esc_html($term->name); // fallback to default
                }
            }
    
            $tags_string = implode(' | ', $translated_tags);
            echo '<div class="tag-list-section country-translated-tags"><p>' . $tags_string . '</p></div>';
        }
    }
    
    
}