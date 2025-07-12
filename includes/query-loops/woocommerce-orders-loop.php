<?php

// === 1. Register Custom Bricks Query: WooCommerce Orders ===

class BricksBooster_Query_Loops_WooCommerce_Orders {

    public function __construct() {
        if ( ! class_exists('WooCommerce') || ! get_option('bricksbooster_woocommerce_orders_query_enabled', 1) ) {
            return;
        }

        add_filter('bricks/setup/control_options', [$this, 'bl_setup_query_controls']);
        add_filter('bricks/query/run', [$this, 'bl_maybe_run_new_queries'], 10, 2);
        add_filter('bricks/query/loop_object', [$this, 'bl_setup_post_data'], 10, 3);

        // Add custom query options in Bricks UI
        add_action('init', [$this, 'add_custom_controls'], 40);
    }

    public function bl_setup_query_controls($control_options) {
        $control_options['queryTypes']['woocommerce_orders'] = esc_html__('WooCommerce Orders', 'bricksbooster');
        return $control_options;
    }

    public function bl_maybe_run_new_queries($results, $query_obj) {
        if ($query_obj->object_type === 'woocommerce_orders') {
            $results = $this->run_new_query($query_obj);
        }
        return $results;
    }

    public function bl_setup_post_data($loop_object, $loop_key, $query_obj) {
        if ($query_obj->object_type === 'woocommerce_orders') {
            global $post;
            $post = get_post($loop_object);
            setup_postdata($post);
        }
        return $loop_object;
    }

    public function add_custom_controls() {
        $elements = ['container', 'block', 'div'];
        foreach ($elements as $name) {
            add_filter("bricks/elements/{$name}/controls", [$this, 'add_woo_order_controls'], 40);
        }
    }

    public function add_woo_order_controls($controls) {
        $woo_controls = [
            'woo_orders_number' => [
                'tab'         => 'content',
                'label'       => esc_html__('Number of Orders', 'bricksbooster'),
                'type'        => 'number',
                'default'     => 10,
                'placeholder' => '10',
                'required'    => [
                    ['query.objectType', '=', 'woocommerce_orders'],
                    ['hasLoop', '!=', false]
                ],
                'rerender' => true,
            ],
            'woo_orders_status' => [
                'tab'      => 'content',
                'label'    => esc_html__('Order Status', 'bricksbooster'),
                'type'     => 'text',
                'default'  => 'wc-completed',
                'required' => [
                    ['query.objectType', '=', 'woocommerce_orders'],
                    ['hasLoop', '!=', false]
                ],
                'rerender' => true,
            ],
            'woo_orders_customer_id' => [
                'tab'      => 'content',
                'label'    => esc_html__('Customer ID', 'bricksbooster'),
                'type'     => 'text',
                'required' => [
                    ['query.objectType', '=', 'woocommerce_orders'],
                    ['hasLoop', '!=', false]
                ],
                'rerender' => true,
            ],
            'woo_orders_date_from' => [
                'tab'      => 'content',
                'label'    => esc_html__('Date From (YYYY-MM-DD)', 'bricksbooster'),
                'type'     => 'text',
                'required' => [
                    ['query.objectType', '=', 'woocommerce_orders'],
                    ['hasLoop', '!=', false]
                ],
                'rerender' => true,
            ],
            'woo_orders_date_to' => [
                'tab'      => 'content',
                'label'    => esc_html__('Date To (YYYY-MM-DD)', 'bricksbooster'),
                'type'     => 'text',
                'required' => [
                    ['query.objectType', '=', 'woocommerce_orders'],
                    ['hasLoop', '!=', false]
                ],
                'rerender' => true,
            ],
        ];

        // Inject after `query` key
        $query_key_index = absint(array_search('query', array_keys($controls)));
        $new_controls = array_slice($controls, 0, $query_key_index + 1, true) + $woo_controls + array_slice($controls, $query_key_index + 1, null, true);

        return $new_controls;
    }

    private function run_new_query($query_obj) {
        $settings = wp_parse_args($query_obj->settings, [
            'woo_orders_number'      => 10,
            'woo_orders_status'      => 'wc-completed',
            'woo_orders_customer_id' => '',
            'woo_orders_date_from'   => '',
            'woo_orders_date_to'     => '',
        ]);

        $args = [
            'limit'      => intval($settings['woo_orders_number']),
            'orderby'    => 'date',
            'order'      => 'DESC',
            'status'     => $settings['woo_orders_status'],
        ];

        // Filter by customer
        if (!empty($settings['woo_orders_customer_id'])) {
            $args['customer_id'] = intval($settings['woo_orders_customer_id']);
        }

        // Filter by date range
        if (!empty($settings['woo_orders_date_from']) || !empty($settings['woo_orders_date_to'])) {
            $args['date_created'] = [
                'after'  => !empty($settings['woo_orders_date_from']) ? $settings['woo_orders_date_from'] : '',
                'before' => !empty($settings['woo_orders_date_to']) ? $settings['woo_orders_date_to'] : '',
            ];
        }

        $orders = wc_get_orders($args);

        // Convert WC_Order objects to post IDs
        return array_map(function ($order) {
            return $order->get_id();
        }, $orders);
    }
}

new BricksBooster_Query_Loops_WooCommerce_Orders();


// === 2. Add Dynamic Tags for WooCommerce Orders ===

add_filter('bricks/dynamic_data/tags', function ($tags) {
    $tags['order_id']            = esc_html__('Order: ID', 'bricksbooster');
    $tags['order_total']         = esc_html__('Order: Total', 'bricksbooster');
    $tags['order_date']          = esc_html__('Order: Date', 'bricksbooster');
    $tags['order_billing_name']  = esc_html__('Order: Billing Name', 'bricksbooster');
    $tags['order_billing_email'] = esc_html__('Order: Billing Email', 'bricksbooster');
    return $tags;
});

add_filter('bricks/dynamic_data/value', function ($value, $tag) {
    global $post;

    if (!is_a($post, 'WP_Post') || $post->post_type !== 'shop_order') {
        return $value;
    }

    $order = wc_get_order($post->ID);
    if (!$order) {
        return $value;
    }

    switch ($tag) {
        case 'order_id':
            return $order->get_id();
        case 'order_total':
            return $order->get_formatted_order_total();
        case 'order_date':
            return $order->get_date_created() ? $order->get_date_created()->date('Y-m-d H:i') : '';
        case 'order_billing_name':
            return $order->get_formatted_billing_full_name();
        case 'order_billing_email':
            return $order->get_billing_email();
    }

    return $value;
}, 10, 2);
