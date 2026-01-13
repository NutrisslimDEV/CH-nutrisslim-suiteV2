<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Nutrisslim_Landing_Content_Widget extends Widget_Base {

    public function get_name() {
        return 'nutrisslim_landing_content_widget';
    }

    public function get_title() {
        return __( 'Nutrisslim Landing Content Widget', 'text-domain' );
    }

    public function get_icon() {
        return 'eicon-comments';
    }

    public function get_categories() {
        return [ 'nutrisslim-landing' ];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'text-domain' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'width_option',
            [
                'label' => __( 'Width', 'text-domain' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'custom',
                'options' => [
                    'default' => __( 'Default', 'text-domain' ),
                    'full-width' => __( 'Full Width', 'text-domain' ),
                    'custom' => __( 'Custom', 'text-domain' ),
                ],
            ]
        );
    
        $this->add_control(
            'custom_width',
            [
                'label' => __( 'Custom Width', 'text-domain' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => 320,
                        'max' => 1920,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 1715,
                ],
                'condition' => [
                    'width_option' => 'custom',
                ],
            ]
        );        
    
        $this->end_controls_section();
    }

    protected function render() {

    $product = get_field('selected_product');
    $product = $product[0];
    if (! $product) {
        return;
    }
    $productObj = wc_get_product($product);
    $type = $productObj->get_type();
    if ($type == 'nutrisslim') {
        $real = true;
    } else {
        $real = '';
    }
?>
<div class="grid-2_sm-1 woocommerce">
    <div class="col imgHolder">
        <?php 
               $prices_include_tax = get_option('woocommerce_prices_include_tax') === 'yes';
               $regular_price = $prices_include_tax
               ? $productObj->get_regular_price()
               : wc_get_price_including_tax($productObj, ['price' => $productObj->get_regular_price()]);               
                $quantities = get_field('quantity_prices', $product);
               // Get custom price for one with tax
               $price_for_one = get_custom_product_price($product, 1, get_the_ID(), '', $real);
               $price_for_one = wc_get_price_including_tax($productObj, ['price' => $price_for_one]);      

                


                if ($regular_price > 0 && $price_for_one) {
                    $discount_percentage = (($regular_price - $price_for_one) / $regular_price) * 100;    
                    echo '<span class="onsale">-' . round($discount_percentage) . '%</span>';
                }
                get_product_image($product);
                echo '<div class="tag-list-section"><p>';
                $user_country = isset($_COOKIE['user_country']) ? strtoupper(sanitize_text_field($_COOKIE['user_country'])) : 'DE';
$allowed = ['DE', 'IT', 'FR'];
if (!in_array($user_country, $allowed)) {
    $user_country = 'DE';
}

$tag_field_map = [
    'DE' => 'tag_de',
    'IT' => 'tag_it',
    'FR' => 'tag_fr',
];

$acf_field_key = $tag_field_map[$user_country];

$terms = get_the_terms($product, 'product_tag');

if ($terms && !is_wp_error($terms)) {
    $translated_tags = [];

    foreach ($terms as $term) {
        $translated = get_field($acf_field_key, 'product_tag_' . $term->term_id);
        if ($translated) {
            $translated_tags[] = esc_html($translated);
        } else {
            $translated_tags[] = esc_html($term->name); // fallback
        }
    }

    echo implode(' | ', $translated_tags);
}

                echo '</p></div>';


                $main_review = get_field('main_review', $product); // Assuming 'main_review' is a repeater field
                // Ensure $main_review is always an array to prevent errors
                if ($main_review['review']) {
                $image_url = wp_get_attachment_image_url($main_review['image'], 'medium');
            ?>
        <div class="mainReview primary-transparent-bg-color">
            <div class="inner">
                <?php echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($review['name']) . '">'; ?>
                <div class="revContent">
                    <div class="name"><?php echo $main_review['name']?></div>
                    <div class="rateMeta">
                        <img class="star" src="/wp-content/uploads/2024/03/star.png" /><img class="star"
                            src="/wp-content/uploads/2024/03/star.png" /><img class="star"
                            src="/wp-content/uploads/2024/03/star.png" /><img class="star"
                            src="/wp-content/uploads/2024/03/star.png" /><img class="star"
                            src="/wp-content/uploads/2024/03/star.png" />
                        <div class="rate"><?php echo $main_review['rate']?> / 5</div>
                        <div class="checker"><span class="check"><img
                                    src="/wp-content/uploads/2024/03/whiteCheck.png" /></span>
                            <?php echo __('Utilisateur vérifié', 'nutrisslim-suite'); ?></div>
                    </div>
                    <div class="revComment">
                        <p><?php echo $main_review['review']?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
    <div class="col contHolder product-short-description">
    <?php
// Detect user country from cookie
$user_country = isset($_COOKIE['user_country']) ? strtoupper(sanitize_text_field($_COOKIE['user_country'])) : 'DE';
$allowed = ['DE', 'IT', 'FR'];
if (!in_array($user_country, $allowed)) {
    $user_country = 'DE';
}

// Map to title meta keys
$title_meta_keys = [
    'DE' => '_product_title_de',
    'IT' => '_product_title_it',
    'FR' => '_product_title_fr',
];

// Get the custom title
$custom_title = get_post_meta($product, $title_meta_keys[$user_country], true);

// Fallback to default name
if (empty($custom_title)) {
    $custom_title = $productObj->get_name();
}

echo '<h2>' . esc_html($custom_title) . '</h2>';
?>

        <?php
                $subtitle = get_field('subtitle', $product);

                // Display the product short description
                echo '<div class="product-short-description">';
                if ($subtitle) {
                    echo '<p class="subtitle">' . $subtitle . '</p>';
                }
                echo do_shortcode('[product_rating id="' . $product . '"]');
                // Detect user country from cookie
$user_country = isset($_COOKIE['user_country']) ? strtoupper(sanitize_text_field($_COOKIE['user_country'])) : 'DE';
$allowed = ['DE', 'IT', 'FR'];
if (!in_array($user_country, $allowed)) {
    $user_country = 'DE';
}

// Map to meta key
$short_description_keys = [
    'DE' => '_product_short_description_de',
    'IT' => '_product_short_description_it',
    'FR' => '_product_short_description_fr',
];
$meta_key = $short_description_keys[$user_country];

// Get localized short description
$custom_short_desc = get_post_meta($product, $meta_key, true);

// Fallback if empty
if (empty($custom_short_desc)) {
    $custom_short_desc = $productObj->get_short_description();
}

echo apply_filters('the_content', $custom_short_desc);

                echo '</div>';

                // Initialize variables to store weight and consumption period
                $weightOutput = '';
                $consumptionPeriodOutput = '';

                // Check and format the weight value if it exists
                $weightValue = $productObj->get_weight();
                if ( !empty($weightValue) ) {
                    $weightOutput = "Net " . $weightValue . " " . get_option( 'woocommerce_weight_unit' );
                }

                // Check and format the consumption period if it exists
                $consumption_period = get_field( 'consumption_period', $product );
                if ( !empty($consumption_period) ) {
                    $consumptionPeriodOutput = sprintf( __('pour %s jours', 'nutrisslim-suite'), $consumption_period );
                }

                // Combine the output strings
                $combinedOutput = $weightOutput;
                if ( !empty($weightOutput) && !empty($consumptionPeriodOutput) ) {
                    // Both values are available
                    $combinedOutput .= " | " . $consumptionPeriodOutput;
                } elseif ( empty($weightOutput) && !empty($consumptionPeriodOutput) ) {
                    // Only consumption period is available
                    $combinedOutput = $consumptionPeriodOutput;
                }

                // Display the combined output
                if ( !empty($combinedOutput) ) {
                    echo '<div class="product-details"><strong>' . $combinedOutput . '</strong></div>';
                }
                
                echo '<p class="redno">' . __('Prix régulier', 'woocommerce') . ':<br /><span>' . wc_price($regular_price) . '</span></p>';
                echo '<div class="price-main price-large">' . wc_price($price_for_one) . '</div>';
                echo '<p><a href="#order-form-anchor" class="org-btn">' . __('Commander maintenant', 'woocommerce') . '</a></p>';
                echo '<p class="prihrani">' . __('et économisez', 'nutrisslim-suite') . ' ' . wc_price($regular_price - $price_for_one) . '</p>';
                echo '<p>' . __('Achat 100 % sécurisé avec une politique de retour sans poser de questions.', 'nutrisslim-suite') . '</p>';
            ?>
    </div>
</div>
<?php
    }
}