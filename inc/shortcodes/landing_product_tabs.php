<?php
function display_custom_product_tabs() {
    // Get selected product from ACF (or however it's being pulled)
    $productref = get_field('selected_product');
    if (empty($productref)) return 'No product selected';

    $productID = $productref[0];
    $product = wc_get_product($productID);
    if (!$product) return 'Invalid product';

    // Detect country from cookie
    $country = isset($_COOKIE['user_country']) ? strtoupper($_COOKIE['user_country']) : 'DE';
    $allowed = ['DE', 'IT', 'FR'];
    if (!in_array($country, $allowed)) $country = 'DE';

    // Fetch data from your plugin fields (not ACF)
    $uporaba    = get_post_meta($productID, "_uporaba_{$country}", true);
    $sestavine  = get_post_meta($productID, "_sestavine_{$country}", true);
    $hranilne   = get_post_meta($productID, "_hranilne_vrednosti_{$country}", true);
    $warnings   = get_post_meta($productID, "_warnings_{$country}", true);

    if ($warnings) {
        $uporaba .= '<p style="margin-top:.9rem;"><strong>' . __('Warning:', 'nutrisslim-suiteV2') . '</strong></p>';
        $uporaba .= $warnings;
    }

    // Localized tab titles
    $tab_titles = [
        'DE' => ['usage' => 'Verwendung', 'ingredients' => 'Inhaltsstoffe', 'nutritional' => 'Nährwerte'],
        'IT' => ['usage' => 'Utilizzo', 'ingredients' => 'Ingredienti', 'nutritional' => 'Valori nutrizionali'],
        'FR' => ['usage' => 'Utilisation', 'ingredients' => 'Ingrédients', 'nutritional' => 'Valeurs nutritionnelles']
    ];
    $titles = $tab_titles[$country];

    // Build HTML
    $tabLists = '<ul class="tabs wc-tabs" role="tablist">';
    $tabContant = '';

    if ($uporaba) {
        $tabLists .= '<li class="uporaba_tab active" id="tab-title-uporaba" role="tab" aria-controls="tab-uporaba"><a href="#tab-uporaba">' . esc_html($titles['usage']) . '</a></li>';
        $tabContant .= '<div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--uporaba panel entry-content wc-tab" id="tab-uporaba" role="tabpanel">' . wp_kses_post($uporaba) . '</div>';
    }

    if ($sestavine) {
        $tabLists .= '<li class="sestavine_tab" id="tab-title-sestavine" role="tab" aria-controls="tab-sestavine"><a href="#tab-sestavine">' . esc_html($titles['ingredients']) . '</a></li>';
        $tabContant .= '<div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--sestavine panel entry-content wc-tab" id="tab-sestavine" role="tabpanel">' . wp_kses_post($sestavine) . '</div>';
    }

    if ($hranilne) {
        $tabLists .= '<li class="hranilne_tab" id="tab-title-hranilne" role="tab" aria-controls="tab-hranilne"><a href="#tab-hranilne">' . esc_html($titles['nutritional']) . '</a></li>';
        $tabContant .= '<div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--hranilne panel entry-content wc-tab" id="tab-hranilne" role="tabpanel">' . wp_kses_post($hranilne) . '</div>';
    }

    $tabLists .= '</ul>';

    return '<div class="woocommerce"><div class="product"><div class="woocommerce-tabs wc-tabs-wrapper">' . $tabLists . $tabContant . '</div></div></div>';
}

// Register the shortcode with WordPress
add_shortcode('landing_product_tabs', 'display_custom_product_tabs');