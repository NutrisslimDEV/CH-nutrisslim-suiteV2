<?php
// Function to create or update the Submit Review page
function create_or_update_submit_review_page() {
    // Define the page slug and title
    $page_slug = 'submit-review';
    $page_title = __('Prześlij recenzję', 'nutrisslim-suiteV2');

    // Check if the page already exists
    $page = get_page_by_path($page_slug);
    if ($page === null) {
        // Page doesn't exist, so create it
        wp_insert_post(array(
            'post_title'    => $page_title,
            'post_name'     => $page_slug,
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => 1,
        ));
    } else {
        // Page exists, check if the title matches the current language
        if (html_entity_decode($page->post_title) !== html_entity_decode($page_title)) {
            wp_update_post(array(
                'ID'           => $page->ID,
                'post_title'   => $page_title,
            ));
        }
        
    }
}

// Function to get order details by order ID
function get_order_details($order_id) {
    if (!$order_id) {
        return null;
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return null;
    }


    $items = $order->get_items();
    $products = array();

    foreach ($items as $item) {
        $product = $item->get_product();
        $item_total = $item->get_total(); // Get the total price for the item in the order
        if ($product && $item_total > 0) {
            $products[] = array(
                'name' => $product->get_name(),
                'thumbnail' => $product->get_image(),
                'id' => $product->get_id(),
            );
        }
    }

    // Get customer email and name
    $customer_email = $order->get_billing_email();
    $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

    return array(
        'products' => $products,
        'customer_email' => $customer_email,
        'customer_name' => $customer_name,
    );
    
}


// Hook the function to run on any admin page initialization
register_activation_hook(__FILE__, 'create_or_update_submit_review_page');


// Filter the content to add the review form on the Submit Review page
function filter_submit_review_page_content($content) {
    if (is_page('submit-review')) {

        // Get order ID from URL parameter
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $order_details = get_order_details($order_id);
    
        // Check if order details are available
        if ($order_details) {
            $products = $order_details['products'];
            $customer_email = $order_details['customer_email'];
            $customer_name = $order_details['customer_name'];
            
            // Format the product IDs
            $product_ids = array_column($products, 'id');
            $formatted_product_ids = implode(',', $product_ids);
        } else {
            $products = array();
            $customer_email = '';
            $customer_name = '';
            $formatted_product_ids = '';
        }     

        ob_start();       
        // SKRBIMO ZA VAŠE MNENJE
        // <p>Pri Nature’s Finest si vedno želimo zagotoviti najboljše izdelke in storitve za naše cenjene stranke. Bi radi ocenili svoj najnovejši nakup?</p>
        // <p>Kot zahvalo za vaš čas, vam bomo skupaj z vašim naročilom poslali posebno darilo!</p>
        // Oceni in pregledaj svoj nakup
        // Povejte nam, kaj mislite
        // Oddaj
    ?>

    <div class="mass-reviews container container-px">
        <div class="review-intro">
            <h1 class="bold"><?php echo __('Zależy nam na Twojej opinii', 'nutrisslim-suiteV2'); ?></h1>
            <p><?php echo __('W Nature\'s Finest zawsze staramy się dostarczać najlepsze produkty i usługi dla naszych cenionych klientów. Czy chciałbyś ocenić swój ostatni zakup?', 'nutrisslim-suiteV2'); ?></p>
            <p><?php echo __('Jako podziękowanie za Twój czas, wyślemy Ci specjalny upominek z Twoim zamówieniem!', 'nutrisslim-suiteV2'); ?></p>
            <h4><?php echo __('Twoje zamówienie', 'nutrisslim-suiteV2'); ?></h4>
            <div class="products_holder grid-3_sm-2_xs-2">
                <?php
                    foreach ($products as $product) {
                        echo '<div class="product_item col grid-middle-noGutter">';
                        echo '<div class="col-5_xs-4">' . $product['thumbnail'] . '</div>';
                        echo '<div class="col-7_xs-8"><p><strong>' . $product['name'] . '</strong></p></div>';
                        echo '</div>';
                    }
                ?>
            </div>
            <h4><?php echo __('Oceń i zrecenzuj swój zakup', 'nutrisslim-suiteV2'); ?></h4>
        </div>
        <div class="form">
            <div class="nf-review_form">
                <div class="review_fields">
                    <input id="review_product_ids" type="hidden" name="review_product_ids" value="<?php echo htmlspecialchars($formatted_product_ids); ?>">
                    <input id="review_customer_email" type="hidden" name="review_customer_email" value="<?php echo htmlspecialchars($customer_email); ?>">
                    <input id="review_customer_name" type="hidden" name="review_customer_name" value="<?php echo htmlspecialchars($customer_name); ?>">
                    <div class="stars">
                        <div class="star">1</div>
                        <div class="star">2</div>
                        <div class="star">3</div>
                        <div class="star">4</div>
                        <div class="star active">5</div>
                    </div>
                    <select name="review_rating" id="review_rating" style="display: none;">
                        <option value="5">5 stars</option>
                        <option value="4">4 stars</option>
                        <option value="3">3 stars</option>
                        <option value="2">2 stars</option>
                        <option value="1">1 star</option>
                    </select>
                    <textarea name="review_review" id="review_text" cols="30" rows="5" placeholder="<?php echo __('Powiedz nam, co myślisz','nutrisslim-suiteV2'); ?>"></textarea>
                    <button id='send_reviews'><?php echo __("Prześlij","woocommerce"); ?></button>
                </div>
            </div> 
        </div>
    </div>           
    <?php
        $content .= ob_get_clean();
    }

    return $content;
}

// Add the content filter for the Submit Review page
add_filter('the_content', 'filter_submit_review_page_content');


// Add custom class to body if it's the "Submit a review" page
function add_custom_body_class($classes) {
    if (is_page('submit-review')) {
        $classes[] = 'submit-review';
    }
    return $classes;
}
add_filter('body_class', 'add_custom_body_class');

// Handle the AJAX request to submit reviews
function handle_submit_reviews() {
    if (!isset($_POST['review_product_ids'], $_POST['review_rating'], $_POST['review_review'], $_POST['review_customer_email'], $_POST['review_customer_name'])) {
        wp_send_json_error('Missing required parameters.');
    }

    $review_product_ids = array_map('intval', $_POST['review_product_ids']);
    $review_rating = intval($_POST['review_rating']);
    $review_review = sanitize_text_field($_POST['review_review']);
    $review_customer_email = sanitize_email($_POST['review_customer_email']);
    $review_customer_name = sanitize_text_field($_POST['review_customer_name']);

    foreach ($review_product_ids as $product_id) {
        $commentdata = array(
            'comment_post_ID' => $product_id,
            'comment_author' => $review_customer_name,
            'comment_author_email' => $review_customer_email,
            'comment_content' => $review_review,
            'comment_type' => 'review',
            'comment_approved' => 0,
        );

        $comment_id = wp_insert_comment($commentdata);

        if ($comment_id) {
            // Add the rating as a comment meta field
            update_comment_meta($comment_id, 'rating', $review_rating);

            // Optionally, you can also update the product's average rating and review count here
            $rating_count = get_post_meta($product_id, '_wc_review_count', true);
            $average_rating = get_post_meta($product_id, '_wc_average_rating', true);

            $new_rating_count = $rating_count ? $rating_count + 1 : 1;
            $new_average_rating = $average_rating ? (($average_rating * $rating_count) + $review_rating) / $new_rating_count : $review_rating;

            update_post_meta($product_id, '_wc_review_count', $new_rating_count);
            update_post_meta($product_id, '_wc_average_rating', $new_average_rating);
        } else {
            wp_send_json_error('Failed to insert review for product ID: ' . $product_id);
        }
    }

    wp_send_json_success('Recenzje zostały pomyślnie przesłane.');
}

add_action('wp_ajax_submit_reviews', 'handle_submit_reviews');
add_action('wp_ajax_nopriv_submit_reviews', 'handle_submit_reviews');
