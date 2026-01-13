<?php
function product_rating_shortcode($atts) {
    // Extract shortcode attributes with a default fallback
    $atts = shortcode_atts(array(
        'id' => null
    ), $atts, 'product_rating');

    // Use global post if no ID is specified and ensure it's a product page
    if (null === $atts['id']) {
        global $post;
        if ('product' === $post->post_type) {
            $atts['id'] = $post->ID;
        } else {
            return ''; // Return empty if not a product page and no ID provided
        }
    }

    // Get the product
    $product = wc_get_product($atts['id']);
    if (!$product) {
        return 'Product not found.';
    }

    // Retrieve the SKU
    $sku = get_post_meta($atts['id'], '_sku', true);

    // Fetch data from your external API or cache
    // (Adjust this function name/path to match your actual code)
    $review_data = get_review_data_with_cache($sku);

    // Fallback if no external data found
    if (!$review_data) {
        $review_data['review_count'] = $product->get_review_count();
        $review_data['average_rate'] = round($product->get_average_rating(), 2);
    }

    // ---- NEW PART: Determine user country from cookie ----
    // Default to 'EN' if cookie is not set or is invalid
    $user_country = isset($_COOKIE['user_country']) ? strtoupper(sanitize_text_field($_COOKIE['user_country'])) : 'EN';

    // Define translations for singular/plural “Verified review(s)” in each language
    $translations = [
        'DE' => [
            'singular' => 'Verifizierte Bewertung',
            'plural'   => 'Verifizierte Bewertungen',
        ],
        'FR' => [
            'singular' => 'Avis vérifié',
            'plural'   => 'Avis vérifiés',
        ],
        'IT' => [
            'singular' => 'Recensione verificata',
            'plural'   => 'Recensioni verificate',
        ],
        'EN' => [
            'singular' => 'Verified review',
            'plural'   => 'Verified reviews',
        ],
    ];

    // Pick the correct translation set; fallback to 'EN' if unknown
    $lang = isset($translations[$user_country]) ? $translations[$user_country] : $translations['EN'];

    $count = isset($review_data['review_count']) ? (int)$review_data['review_count'] : 0;
    $average_rate = isset($review_data['average_rate']) ? $review_data['average_rate'] : 0;

    // Start building the output
    $output = '';

    // Only show if there is at least 1 review
    if ($count > 0) {
        // Prepare the rating HTML with stars
        $output .= '<div class="rateMeta">';
        // Display 5 star images (or use a dynamic star rating if you prefer)
        $output .= '<img class="star" src="/wp-content/uploads/2024/03/star.png">'
                 . '<img class="star" src="/wp-content/uploads/2024/03/star.png">'
                 . '<img class="star" src="/wp-content/uploads/2024/03/star.png">'
                 . '<img class="star" src="/wp-content/uploads/2024/03/star.png">'
                 . '<img class="star" src="/wp-content/uploads/2024/03/star.png">';

        // Display average rating
        $output .= '<div class="rate">' . $average_rate . ' / 5</div>';

        // Decide whether to use singular or plural text
        if ($count === 1) {
            $verified_text = $lang['singular']; // e.g. 'Verifizierte Bewertung'
        } else {
            $verified_text = $lang['plural'];   // e.g. 'Verifizierte Bewertungen'
        }

        // Example: “2 Verifizierte Bewertungen”
        $output .= '<div class="checker"><span class="check">'
                 . '<img src="/wp-content/uploads/2024/03/whiteCheck.png"></span> '
                 . $count . ' ' . $verified_text
                 . '</div>';

        $output .= '</div>'; // Close .rateMeta
    }

    return $output;
}
add_shortcode('product_rating', 'product_rating_shortcode');
