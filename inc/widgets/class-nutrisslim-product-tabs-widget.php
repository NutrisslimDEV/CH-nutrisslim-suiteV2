<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Nutrisslim_Product_Tabs_Widget extends Widget_Base {

    public function get_name() {
        return 'nutrisslim-product-tabs';
    }

    public function get_title() {
        return __( 'Nutrisslim Product Tabs', 'nutrisslim-elementor-widgets' );
    }

    public function get_icon() {
        return 'eicon-tabs';
    }

    public function get_categories() {
        return [ 'nutrisslim' ];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'section_tab',
            [
                'label' => __( 'Settings', 'nutrisslim-elementor-widgets' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        global $product;
    
        if (!is_a($product, 'WC_Product')) {
            echo 'Please set a valid product.';
            return;
        }
    
        $user_country = isset($_COOKIE['user_country']) ? sanitize_text_field($_COOKIE['user_country']) : 'DE';
        $allowed_countries = ['DE', 'IT', 'FR'];
        if (!in_array($user_country, $allowed_countries)) {
            $user_country = 'DE'; // Default to DE
        }
    
        // Retrieve the content from custom fields based on country
        $uporaba_content = get_post_meta($product->get_id(), "_uporaba_{$user_country}", true);
        $sestavine_content = get_post_meta($product->get_id(), "_sestavine_{$user_country}", true);
        $hranilne_vrednosti_content = get_post_meta($product->get_id(), "_hranilne_vrednosti_{$user_country}", true);
        $warnings_content = get_post_meta($product->get_id(), "_warnings_{$user_country}", true);
    
        $tabs = apply_filters('woocommerce_product_tabs', array());
    
        if (isset($tabs['reviews'])) {
            unset($tabs['reviews']);
        }
    
        unset($tabs['description']);
    
        if (!empty($uporaba_content)) {
            $tabs['uporaba'] = array(
                'title'    => __('Usage', 'nutrisslim-suiteV2'),
                'callback' => 'nutrisslim_uporaba_tab_content_callback',
                'priority' => 10,
            );
        }
    
        if (!empty($sestavine_content)) {
            $tabs['sestavine'] = array(
                'title'    => __('Ingredients', 'nutrisslim-suiteV2'),
                'callback' => 'nutrisslim_sestavine_tab_content_callback',
                'priority' => 20,
            );
        }
    
        if (!empty($hranilne_vrednosti_content)) {
            $tabs['hranilne_vrednosti'] = array(
                'title'    => __('Nutritional values', 'nutrisslim-suiteV2'),
                'callback' => 'nutrisslim_hranilne_vrednosti_tab_content_callback',
                'priority' => 30,
            );
        }
    
        $firstItem = array_shift($tabs);
        array_push($tabs, $firstItem);
    
        if (!empty($tabs)) : ?>
        <div class="woocommerce-tabs wc-tabs-wrapper">
            <ul class="tabs wc-tabs" role="tablist">
                <?php foreach ($tabs as $key => $tab) : ?>
                <li class="<?php echo esc_attr($key); ?>_tab" id="tab-title-<?php echo esc_attr($key); ?>" role="tab"
                    aria-controls="tab-<?php echo esc_attr($key); ?>">
                    <a
                        href="#tab-<?php echo esc_attr($key); ?>"><?php echo apply_filters('woocommerce_product_' . $key . '_tab_title', esc_html($tab['title']), $key); ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php foreach ($tabs as $key => $tab) : ?>
            <div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr($key); ?> panel entry-content wc-tab"
                id="tab-<?php echo esc_attr($key); ?>" role="tabpanel"
                aria-labelledby="tab-title-<?php echo esc_attr($key); ?>">
                <?php if (isset($tab['callback'])) { call_user_func($tab['callback'], $key, $tab, $user_country); } ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif;
    }
}

function nutrisslim_uporaba_tab_content_callback($key, $tab, $country) {
    echo get_post_meta(get_the_ID(), "_uporaba_{$country}", true);
    $warnings = get_post_meta(get_the_ID(), "_warnings_{$country}", true);
    if ($warnings) {
        echo '<p><strong>' . __( 'Warning:', 'nutrisslim-suiteV2' ) . '</strong></p>';
        echo $warnings;
    }    
}

function nutrisslim_sestavine_tab_content_callback($key, $tab, $country) {
    echo get_post_meta(get_the_ID(), "_sestavine_{$country}", true);
}

function nutrisslim_hranilne_vrednosti_tab_content_callback($key, $tab, $country) {
    echo get_post_meta(get_the_ID(), "_hranilne_vrednosti_{$country}", true);
}
