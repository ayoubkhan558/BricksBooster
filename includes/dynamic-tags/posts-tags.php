<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class BricksBooster_Dynamic_Posts_Tags {

	public function __construct() {
		add_filter( 'bricks/dynamic_tags_list', [ $this, 'add_my_tags_to_builder' ] );
		add_filter( 'bricks/dynamic_data/render_tag', [ $this, 'get_my_tag_value' ], 20, 3 );
		add_filter( 'bricks/dynamic_data/render_content', [ $this, 'render_my_tags' ], 20, 3 );
		add_filter( 'bricks/frontend/render_data', [ $this, 'render_my_tags' ], 20, 2 );
	}

	public function add_my_tags_to_builder( $tags ) {
		$custom_tags = [
			'bb_post_reading_time'   => 'Post Reading Time',
			'bb_post_word_count'     => 'Post Word Count',
			'bb_post_char_count'     => 'Post Character Count',
			'bb_post_excerpt_words'  => 'Post Excerpt Word Count',
			'bb_post_first_image'    => 'Post First Image URL',
		];

		foreach ( $custom_tags as $key => $label ) {
			$tags[] = [
				'name'  => '{' . $key . '}',
				'label' => $label,
				'group' => 'BricksBooster Tags',
			];
		}

		return $tags;
	}

	public function get_my_tag_value( $tag, $post, $context = 'text' ) {
		$clean_tag = str_replace( [ '{', '}' ], '', $tag );

		switch ( $clean_tag ) {
			case 'bb_post_reading_time':
				return $this->calculate_reading_time( $post );

			case 'bb_post_word_count':
				return $this->get_word_count( $post );

			case 'bb_post_char_count':
				return $this->get_char_count( $post );

			case 'bb_post_excerpt_words':
				return $this->get_excerpt_word_count( $post );

			case 'bb_post_first_image':
				return $this->get_first_image_url( $post );

			default:
				return $tag;
		}
	}

	public function render_my_tags( $content, $post, $context = 'text' ) {
		$replacements = [
			'{bb_post_reading_time}'   => $this->calculate_reading_time( $post ),
			'{bb_post_word_count}'     => $this->get_word_count( $post ),
			'{bb_post_char_count}'     => $this->get_char_count( $post ),
			'{bb_post_excerpt_words}'  => $this->get_excerpt_word_count( $post ),
			'{bb_post_first_image}'    => $this->get_first_image_url( $post ),
		];

		foreach ( $replacements as $tag => $value ) {
			if ( strpos( $content, $tag ) !== false ) {
				$content = str_replace( $tag, $value, $content );
			}
		}

		return $content;
	}

	private function calculate_reading_time( $post ) {
		if ( ! $post || ! isset( $post->ID ) ) return '';
		$content = wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $post->ID ) ) );
		$word_count = str_word_count( $content );
		$reading_time = max( 1, ceil( $word_count / 200 ) );
		return $reading_time . ' ' . _n( 'minute read', 'minutes read', $reading_time, 'bricks-booster' );
	}

	private function get_word_count( $post ) {
		if ( ! $post || ! isset( $post->ID ) ) return '';
		$content = wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $post->ID ) ) );
		return str_word_count( $content );
	}

	private function get_char_count( $post ) {
		if ( ! $post || ! isset( $post->ID ) ) return '';
		$content = wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $post->ID ) ) );
		return strlen( $content );
	}

	private function get_excerpt_word_count( $post ) {
		if ( ! $post || ! isset( $post->ID ) ) return '';
		$excerpt = wp_strip_all_tags( get_the_excerpt( $post->ID ) );
		return str_word_count( $excerpt );
	}

	private function get_first_image_url( $post ) {
		if ( ! $post || ! isset( $post->ID ) ) return '';
		$content = get_post_field( 'post_content', $post->ID );
		preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $content, $matches );
		return isset( $matches[1] ) ? esc_url( $matches[1] ) : '';
	}
}

// Instantiate the class
// new BricksBooster_Dynamic_Posts_Tags();
