<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Sticky_Add_To_Cart_Widget extends Widget_Base {

    public function get_name() {
        return 'sticky_add_to_cart_widget';
    }

    public function get_title() {
        return __( 'Sticky Add To Cart', 'text-domain' );
    }

    public function get_icon() {
        return 'eicon-cart';
    }

    public function get_categories() {
        return [ 'nutrisslim-landing' ];
    }

    protected function render() {

        $product = get_field('selected_product');

    // If the field is empty or not an array, bail
    if (empty($product) || !is_array($product) || empty($product[0])) {
        return;
    }

    $product_id = $product[0];
    $productObj = wc_get_product($product_id);

    // If the product couldn't be retrieved, bail
    if (!$productObj) {
        return;
    }

    $type = $productObj->get_type();
    $real = ($type === 'nutrisslim') ? true : '';

    $regular_price = wc_get_price_including_tax($productObj, [
        'price' => $productObj->get_regular_price(),
    ]);

    $price_for_one = get_custom_product_price($product_id, 1, get_the_ID(), '', $real);
    $price_for_one_with_tax = wc_get_price_including_tax($productObj, [
        'price' => $price_for_one,
    ]);
        
     

        ?>
<div class="sticky-add-to-cart">
    <span class="regular-price"><?php echo wc_price($regular_price); ?></span>
    <span class="sale-price"><?php echo wc_price($price_for_one_with_tax); ?></span>

    <a href="#order-form-anchor" class="org-btn"><?php _e('ORDER NOW', 'nutrisslim-suiteV2'); ?></a>
</div>
<script>
$ = jQuery.noConflict();
$(function() {
    var $this = $('div.sticky-add-to-cart');
    var $stickyContainer = $this.closest('.e-con').addClass('sticky-container');

    $(window).on('scroll', function() {
        var orderFormAnchor = $('#order-form-anchor');
        if (orderFormAnchor.length) {
            var orderFormOffset = orderFormAnchor.offset().top;
            var scrollTop = $(window).scrollTop();
            var windowHeight = $(window).height();

            if (scrollTop + windowHeight >= orderFormOffset) {
                $stickyContainer.addClass('slide-down');
            } else {
                $stickyContainer.removeClass('slide-down');
            }
        }
    });

});
</script>
<?php
    }
}