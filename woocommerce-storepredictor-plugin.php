<?php

/**
 * Plugin Name: storePredictor
 * Plugin URI: https://www.storepredictor.com/integrations
 * Description: storePredictor Integration to track data for predictive analytics.
 * Author: storePredictor.com
 * Author URI: https://www.storepredictor.com
 * Version: 1.0
 */
if (!class_exists('WC_storePredictor_plugin')) :
    class WC_storePredictor_plugin
    {
        /**
         * Construct the plugin.
         */
        public function __construct()
        {
            add_action('plugins_loaded', [$this, 'init']);
        }

        /**
         * Initialize the plugin.
         */
        public function init()
        {
            // Checks if WooCommerce is installed.
            if (class_exists('WC_Integration')) {
                // Include our integration class.
                require_once 'class-wc-storepredictor-integration.php';

                // Register the integration.
                add_filter('woocommerce_integrations', [$this, 'add_integration']);

                // Set the plugin slug
                define('STOREPREDICTOR', 'wc-settings');

                // Setting action for plugin
                add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'WC_storePredictor_plugin_action_links');

                // create new menu item
                add_action('admin_menu', 'sp_setup_menu');
                add_action( 'admin_head', 'admin_menu_add_external_links_as_submenu_jquery' );

                /**
                 * Add custom tracking code to the thank-you page
                 */
                add_action('woocommerce_thankyou', 'sp_order_tracking');

                add_action('wp_head', 'sp_tracking_javascript');

                function sp_setup_menu()
                {
                    global $submenu;
                    $menu_slug = 'sp';
                    add_menu_page('storePredictor', 'storePredictor', 'manage_options', $menu_slug, '');

                    // add the external links to the slug you used when adding the top level menu
                    $submenu[$menu_slug][] = array('<div id="sspApp">Application</div>', 'manage_options', 'https://my.storepredictor.com/');
                    $submenu[$menu_slug][] = array('<div id="spWeb">Website</div>', 'manage_options', 'https://www.storepredictor.com/');
                }

                function WC_storePredictor_plugin_action_links($links)
                {

                    $links[] = '<a href="' . menu_page_url(STOREPREDICTOR, false) . '&tab=integration&section=storepredictor-integration">Setting</a>';
                    return $links;
                }

                function sp_tracking_javascript()
                {
                    $spCode = get_option('woocommerce_storepredictor-integration_settings')['sp_code'];
                    ?>
                    <!-- storePredictor track for <?php echo esc_attr($spCode); ?>  -->
                    <script type="text/javascript">
                        (function (i, s, o, g, r, a, m) {
                            i['StorePredictorObjectV2'] = r;
                            i[r] = i[r] || function () {
                                (i[r].q = i[r].q || []).push(arguments)
                            }, i[r].l = 1 * new Date();
                            a = s.createElement(o),
                                m = s.getElementsByTagName(o)[0];
                            a.async = 1;
                            a.src = g;
                            m.parentNode.insertBefore(a, m)
                        })(window, document, 'script', 'https://cdn.storepredictor.com/sp.v2.js', 'sp');
                        sp('create', '<?php echo esc_attr($spCode); ?>', 'auto');
                    </script>
                    <!-- storePredictor track visitor -->
                    <script>sp('send', 'pageview', '');</script>
                    <?php
                    if (is_product()) {
                        global $product;
                        if ($product->get_sku()) {
                            ?>
                            <!-- storePredictor track product -->
                            <script>sp('send', 'pageview', '<?php echo esc_attr($product->get_sku()); ?>');</script>
                        <?php
                            }
                    }
                }

                function sp_order_tracking($order_id)
                {

                    // Lets grab the order
                    $order = wc_get_order($order_id);

                    /**
                     * Put your tracking code here
                     * You can get the order total etc e.g. $order->get_total();
                     */

                    // This is the order total
                    $orderTotal = $order->get_total();

                    // This is how to grab line items from the order
                    $line_items = $order->get_items();

                    $items = [];

                    // This loops over line items
                    foreach ($line_items as $item) {
                        // This will be a product
                        $product = $order->get_product_from_item($item);

                        // This is the products SKU
                        $sku = $product->get_sku();

                        // This is the qty purchased
                        $qty = $item['qty'];

                        // Line item total cost including taxes and rounded
                        $total = $order->get_line_total($item, true, true);

                        $items[] = [
                            "productCode" => $sku,
                            "unitPrice" => $total,
                            "quantity" => $qty
                        ];
                    }
                    $currSymbol = $order->get_currency();
                    $i = 0;
                    ?>
                    <!-- storePredictor track order  -->
                    <script>
                        sp('create', '', 'auto');
                        sp('send', 'order', { orderId: '<?php echo esc_attr($order_id); ?>', items: [<?php foreach($items as $item) { ?> {productCode: '<?php echo esc_attr($item['productCode']); ?>', unitPrice: <?php echo esc_attr($item['unitPrice']); ?>, quantity: <?php echo esc_attr($item['quantity']); ?> } <?php if (count($items) -1 !== $i) { ?>, <?php } ?> <?php $i++;} ?>], totalPrice: <?php echo esc_attr($orderTotal); ?>, currency: '<?php echo esc_attr($currSymbol); ?>' });
                    </script>
                    <?php
                }

                function admin_menu_add_external_links_as_submenu_jquery()
                {
                    ?>
                    <script type="text/javascript">
                        jQuery(document).ready( function($) {
                            $('#spApp').parent().attr('target','_blank');
                            $('#spWeb').parent().attr('target','_blank');
                        });
                    </script>
                    <?php
                }
            }
        }

        /**
         * Add a new integration to WooCommerce.
         */
        public function add_integration($integrations)
        {
            $integrations[] = 'WC_storePredictor_Integration';
            return $integrations;
        }
    }

    $WC_storepredictor_plugin = new WC_storepredictor_plugin(__FILE__);
endif;