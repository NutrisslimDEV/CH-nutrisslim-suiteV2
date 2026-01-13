<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Nutrisslim_Main_Review_Widget extends Widget_Base {

    public function get_name() {
        return 'nutrisslim_main_review_widget';
    }

    public function get_title() {
        return __( 'Nutrisslim Main Review Widget', 'text-domain' );
    }

    public function get_icon() {
        return 'eicon-comments';
    }

    public function get_categories() {
        return [ 'nutrisslim' ];
    }

    protected function _register_controls() {
        // Register widget controls here if necessary.
    }

    protected function render() {
        // Get country from cookie, default to 'DE' if not set
        $user_country = isset($_COOKIE['user_country']) ? strtolower(sanitize_text_field($_COOKIE['user_country'])) : 'de';
        $allowed_countries = ['de', 'it', 'fr'];
        
        // Validate country code, default to 'de' if invalid
        if (!in_array($user_country, $allowed_countries)) {
            $user_country = 'de';
        }
        
        // Try to get the country-specific main review field
        // If not found, try fallback countries in order: current -> de -> it -> fr
        $main_review = null;
        $countries_to_try = array_unique([$user_country, 'de', 'it', 'fr']);
        
        // Get current post ID if available
        $post_id = get_the_ID();
        if (!$post_id) {
            global $post;
            $post_id = isset($post->ID) ? $post->ID : null;
        }
        
        foreach ($countries_to_try as $country) {
            $field_name = 'main_review_' . $country;
            $review_data = null;
            
            if (!$post_id) {
                continue;
            }
            
            // Use get_post_meta directly - ACF stores group fields as post meta
            // Try the group field name first
            $meta_value = get_post_meta($post_id, $field_name, true);
            if (!empty($meta_value) && is_array($meta_value)) {
                $review_data = $meta_value;
            }
            
            // If group field not found, try individual sub-fields
            // ACF stores group sub-fields with format: {group_field}_{sub_field}
            if (($review_data === false || $review_data === null || !is_array($review_data))) {
                $name_meta = get_post_meta($post_id, $field_name . '_name', true);
                $review_meta = get_post_meta($post_id, $field_name . '_review', true);
                $image_meta = get_post_meta($post_id, $field_name . '_image', true);
                $rate_meta = get_post_meta($post_id, $field_name . '_rate', true);
                
                // Also try without underscore (some ACF versions use different format)
                if (empty($name_meta)) {
                    $name_meta = get_post_meta($post_id, $field_name . 'name', true);
                }
                if (empty($review_meta)) {
                    $review_meta = get_post_meta($post_id, $field_name . 'review', true);
                }
                if (empty($image_meta)) {
                    $image_meta = get_post_meta($post_id, $field_name . 'image', true);
                }
                if (empty($rate_meta)) {
                    $rate_meta = get_post_meta($post_id, $field_name . 'rate', true);
                }
                
                // Build array if we have at least one field
                if (!empty($name_meta) || !empty($review_meta) || !empty($image_meta) || !empty($rate_meta)) {
                    $review_data = [
                        'name' => $name_meta ?: '',
                        'review' => $review_meta ?: '',
                        'image' => $image_meta ?: '',
                        'rate' => $rate_meta ?: ''
                    ];
                }
            }
            
            // Check if we got valid data
            if ($review_data !== false && $review_data !== null && is_array($review_data)) {
                // Check if at least one meaningful field has data
                $has_any_data = !empty($review_data['name']) || !empty($review_data['review']) || 
                               !empty($review_data['text']) || !empty($review_data['image']) || 
                               !empty($review_data['rate']);
                
                if ($has_any_data) {
                    $main_review = $review_data;
                    break;
                }
            }
        }
        
        // Extract values safely (check both 'review' and 'text' keys as ACF might use either)
        $image_url = (!empty($main_review) && !empty($main_review['image'])) ? wp_get_attachment_image_url($main_review['image'], 'medium') : '';
        $name = (!empty($main_review) && !empty($main_review['name'])) ? $main_review['name'] : '';
        $review = (!empty($main_review)) ? (!empty($main_review['review']) ? $main_review['review'] : (!empty($main_review['text']) ? $main_review['text'] : '')) : '';
        $rate = (!empty($main_review) && !empty($main_review['rate'])) ? $main_review['rate'] : '';
        
        // Only show content if we have at least name or review text
        $has_content = !empty($name) || !empty($review);
        
        // Always output container so loader can detect widget completion
        // Hide it if no content
?>
    <div class="mainReview primary-transparent-bg-color"<?php echo !$has_content ? ' style="display:none!important;height:0!important;overflow:hidden!important;margin:0!important;padding:0!important;"' : ''; ?>>
        <?php if ($has_content): ?>
        <div class="inner">
            <?php if ($image_url): ?>
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy">
            <?php endif; ?>
            <div class="revContent">
                <?php if ($name): ?>
                    <div class="name"><?php echo esc_html($name); ?></div>
                <?php endif; ?>
                <div class="rateMeta">
                    <img class="star" src="/wp-content/uploads/2024/03/star.png" loading="lazy" /><img class="star" src="/wp-content/uploads/2024/03/star.png" loading="lazy" /><img class="star" src="/wp-content/uploads/2024/03/star.png" loading="lazy" /><img class="star" src="/wp-content/uploads/2024/03/star.png" loading="lazy" /><img class="star" src="/wp-content/uploads/2024/03/star.png" loading="lazy" />
                    <?php if ($rate): ?>
                        <div class="rate"><?php echo esc_html($rate); ?> / 5</div>
                    <?php endif; ?>
                    <div class="checker"><span class="check"><img src="/wp-content/uploads/2024/03/whiteCheck.png" loading="lazy" /></span> <?php echo __('Zweryfikowany użytkownik', 'nutrisslim-suiteV2'); ?></div>
                </div>
                <?php if ($review): ?>
                    <div class="revComment">
                        <p><?php echo wp_kses_post($review); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
<?php        
    }
}        
