<?php

class BricksBooster_Query_Loops_Comments_Loop {

    public function __construct() {
        add_filter('bricks/setup/control_options', [$this, 'bl_setup_query_controls']);
        add_filter('bricks/query/run', [$this, 'bl_maybe_run_new_queries'], 10, 2);
        add_filter('bricks/query/loop_object', [$this, 'bl_setup_post_data'], 10, 3);
    }

    public function bl_setup_query_controls($control_options) {
        $control_options['queryTypes']['goodmonks_crp_query'] = esc_html__('Comments');
        return $control_options;
    }

    public function bl_maybe_run_new_queries($results, $query_obj) {
        if ($query_obj->object_type === 'goodmonks_crp_query') {
            $results = $this->run_first_query();
        }
        return $results;
    }

    public function bl_setup_post_data($loop_object, $loop_key, $query_obj) {
        if ($query_obj->object_type === 'goodmonks_crp_query') {
            global $post;
            $post = get_post($loop_object);
            setup_postdata($post);
        }
        return $loop_object;
    }

    private function run_first_query() {
        $args = [
            'post_type' => 'page',
            'posts_per_page' => 6,
        ];

        $posts_query = new WP_Query($args);
        return $posts_query->posts;
    }
}