<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Top_List_Section_Widget extends Widget_Base {

    public function get_name() {
        return 'top_list_section';
    }

    public function get_title() {
        return __( 'Top List Section', 'text-domain' );
    }

    public function get_icon() {
        return 'eicon-editor-list-ul';
    }

    public function get_categories() {
        return [ 'nutrisslim' ]; // Assigning the widget to the 'nutrisslim' category
    }

    protected function _register_controls() {
        // You can register widget controls here if needed.
    }

    protected function render() {
        global $post;
        $product = wc_get_product($post->ID);
    
        // Get the user's country from cookie
        $country = isset($_COOKIE['user_country']) ? strtoupper(sanitize_text_field($_COOKIE['user_country'])) : 'DE';
        $allowed_countries = ['DE', 'FR', 'IT'];
        if (!in_array($country, $allowed_countries)) {
            $country = 'DE';
        }
    
        // Get your custom meta data
        $features_data = get_post_meta($post->ID, "_three_column_features_{$country}", true);

if (empty($features_data) || (
    empty($features_data['main_title']) &&
    empty($features_data['list_title_1']) &&
    empty($features_data['features_1']) &&
    empty($features_data['list_title_2']) &&
    empty($features_data['features_2'])
)) {
    return;
}
    
        ?>
        <style>
        .list-section h2 {
            text-align: center;
        }
    
        .lists-container {
            display: flex;
        }
    
        .lists-container .list {
            flex-basis: 35%;
            max-width: 35%;
            padding: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    
        .lists-container .middle-img {
            flex-basis: 30%;
            max-width: 30%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
    
        .lists-container button {
            background-color: #1FB15A;
            border-radius: 35px;
            padding: 8px 40px;
            margin-top: 10px;
            border-color: #0ea44b;
        }
        </style>
        <?php
    
        echo '<div class="list-section top-list-section">';
    
        // Main title
        if (!empty($features_data['main_title'])) {
            echo '<h2>' . esc_html($features_data['main_title']) . '</h2>';
        }
    
        echo '<div class="lists-container">';
    
        // LEFT COLUMN
        echo '<div class="list greenCheckList checkPlus">';
        echo '<div class="inner">';
        if (!empty($features_data['list_title_1'])) {
            echo '<h3>' . esc_html($features_data['list_title_1']) . '</h3>';
        }
        if (!empty($features_data['features_1']) && is_array($features_data['features_1'])) {
            echo '<ul>';
            foreach ($features_data['features_1'] as $feature) {
                echo '<li>' . esc_html($feature) . '</li>';
            }
            echo '</ul>';
        }
        echo '</div>';
        echo '</div>';
    
        // MIDDLE IMAGE + BUTTON (no image, just cart button)
        echo '<div class="middle-img">';
        if (!empty($product)) {
            echo '<form class="addToCartForm">';
            echo '<button class="elementor-button add-to-cart-icon" 
                         data-product-id="' . esc_attr($product->get_id()) . '" 
                         data-quantity="1" 
                         type="submit">'
                    . __('Add to cart', 'nutrisslim-suiteV2') .
                 '</button>';
            echo '</form>';
        }
        echo '</div>';
    
        // RIGHT COLUMN
        echo '<div class="list greenCheckList checkMinus">';
        echo '<div class="inner">';
        if (!empty($features_data['list_title_2'])) {
            echo '<h3>' . esc_html($features_data['list_title_2']) . '</h3>';
        }
        if (!empty($features_data['features_2']) && is_array($features_data['features_2'])) {
            echo '<ul>';
            foreach ($features_data['features_2'] as $feature) {
                echo '<li>' . esc_html($feature) . '</li>';
            }
            echo '</ul>';
        }
        echo '</div>';
        echo '</div>';
    
        echo '</div>'; // .lists-container
        echo '</div>'; // .top-list-section
    }
    
    
}