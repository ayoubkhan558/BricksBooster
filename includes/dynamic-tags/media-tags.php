<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class BricksBooster_Dynamic_Media_Tags {

    public function __construct() {
        add_filter( 'bricks/dynamic_tags_list', [ $this, 'add_media_tags_to_builder' ] );
        add_filter( 'bricks/dynamic_data/render_tag', [ $this, 'get_media_tag_value' ], 20, 3 );
        add_filter( 'bricks/dynamic_data/render_content', [ $this, 'render_media_tags' ], 20, 3 );
        add_filter( 'bricks/frontend/render_data', [ $this, 'render_media_tags' ], 20, 2 );
    }

    public function add_media_tags_to_builder( $tags ) {
        $bb_label = ' [BricksBooster]';
        
        $media_tags = [
            'bb_media_url'         => 'Media URL',
            'bb_media_alt'         => 'Alt Text',
            'bb_media_width'       => 'Image Width',
            'bb_media_height'      => 'Image Height',
            'bb_media_date'        => 'Upload Date',
            'bb_media_title'       => 'Image Title',
            'bb_media_caption'     => 'Image Caption',
            'bb_media_description' => 'Image Description',
            'bb_media_filename'    => 'Filename',
            'bb_media_mime_type'   => 'MIME Type',
            'bb_media_author'      => 'Author ID',
            'bb_media_slug'        => 'Slug',
        ];

        foreach ( $media_tags as $key => $label ) {
            $tags[] = [
                'name'  => '{' . $key . '}',
                'label' => $label . $bb_label,
                'group' => 'Media' . $bb_label,
            ];
        }

        return $tags;
    }

    public function get_media_tag_value( $tag, $post, $context = 'text' ) {
        $clean_tag = str_replace( [ '{', '}' ], '', $tag );
        $post_id = isset( $post->ID ) ? $post->ID : '';
        $attachment_id = get_post_thumbnail_id( $post_id );
        
        if ( ! $attachment_id ) {
            return $tag; // Return original tag if no attachment
        }

        $image_data = wp_get_attachment_metadata( $attachment_id );
        $attachment = get_post( $attachment_id );

        switch ( $clean_tag ) {
            case 'bb_media_url':
                return wp_get_attachment_url( $attachment_id );

            case 'bb_media_alt':
                return get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

            case 'bb_media_width':
                return $image_data['width'] ?? '';

            case 'bb_media_height':
                return $image_data['height'] ?? '';

            case 'bb_media_date':
                return $attachment ? mysql2date( get_option( 'date_format' ), $attachment->post_date ) : '';

            case 'bb_media_title':
                return $attachment->post_title ?? '';

            case 'bb_media_caption':
                return wp_get_attachment_caption( $attachment_id );

            case 'bb_media_description':
                return $attachment->post_content ?? '';

            case 'bb_media_filename':
                return basename( get_attached_file( $attachment_id ) );

            case 'bb_media_mime_type':
                return $attachment->post_mime_type ?? '';

            case 'bb_media_author':
                return $attachment->post_author ?? '';

            case 'bb_media_slug':
                return $attachment->post_name ?? '';

            default:
                return $tag;
        }
    }

    public function render_media_tags( $content, $post, $context = 'text' ) {
        if ( ! is_object( $post ) || ! isset( $post->ID ) ) {
            return $content;
        }

        $attachment_id = get_post_thumbnail_id( $post->ID );
        if ( ! $attachment_id ) {
            return $content;
        }

        $image_data = wp_get_attachment_metadata( $attachment_id );
        $attachment = get_post( $attachment_id );

        $replacements = [
            '{bb_media_url}'         => wp_get_attachment_url( $attachment_id ),
            '{bb_media_alt}'         => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
            '{bb_media_width}'       => $image_data['width'] ?? '',
            '{bb_media_height}'      => $image_data['height'] ?? '',
            '{bb_media_date}'        => $attachment ? mysql2date( get_option( 'date_format' ), $attachment->post_date ) : '',
            '{bb_media_title}'       => $attachment->post_title ?? '',
            '{bb_media_caption}'     => wp_get_attachment_caption( $attachment_id ),
            '{bb_media_description}' => $attachment->post_content ?? '',
            '{bb_media_filename}'    => basename( get_attached_file( $attachment_id ) ),
            '{bb_media_mime_type}'   => $attachment->post_mime_type ?? '',
            '{bb_media_author}'      => $attachment->post_author ?? '',
            '{bb_media_slug}'        => $attachment->post_name ?? '',
        ];

        foreach ( $replacements as $tag => $value ) {
            if ( strpos( $content, $tag ) !== false ) {
                $content = str_replace( $tag, $value, $content );
            }
        }

        return $content;
    }
}