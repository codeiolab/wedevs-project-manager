<?php

/**
 * Description of comments
 *
 * @author weDevs
 */
class CPM_Comment {

    private $_db;
    private static $_instance;

    public function __construct() {
        global $wpdb;

        $this->_db = $wpdb;
    }

    public static function getInstance() {
        if ( !self::$_instance ) {
            self::$_instance = new CPM_Comment();
        }

        return self::$_instance;
    }

    /**
     * Insert a new comment
     * 
     * @param array $commentdata
     * @param int $privacy
     * @param array $files
     * @return int
     */
    function create( $commentdata, $privacy, $files = array() ) {
        $user = wp_get_current_user();

        $commentdata['comment_author_IP'] = preg_replace( '/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR'] );
        $commentdata['comment_agent'] = substr( $_SERVER['HTTP_USER_AGENT'], 0, 254 );
        $commentdata['comment_author'] = $user->display_name;
        $commentdata['comment_author_email'] = $user->user_email;
        $commentdata['comment_type'] = 'project';

        $comment_id = wp_insert_comment( $commentdata );

        if ( $comment_id ) {
            add_comment_meta( $comment_id, '_privacy', $privacy );
            add_comment_meta( $comment_id, '_files', $files );
        }

        do_action( 'cpm_new_comment', $comment_id, $commentdata );

        return $comment_id;
    }

    /**
     * Update a comment
     *
     * @param array $data
     * @param int $comment_id
     */
    function update( $data, $comment_id ) {
        wp_update_comment( array(
            'comment_ID' => $comment_id,
            'comment_content' => $data['text']
        ) );

        update_comment_meta( $comment_id, '_privacy', $data['privacy'] );
    }

    /**
     * Delete a comment
     *
     * @param int $comment_id
     * @param bool $force_delete
     */
    function delete( $comment_id, $force_delete = false ) {
        wp_delete_comment( $comment_id, $force_delete );
    }

    /**
     * Get a single comment
     *
     * @param int $comment_id
     * @return object
     */
    function get( $comment_id ) {
        $files_meta = get_comment_meta( $comment_id, '_files', true );
        $comment = get_comment( $comment_id );
        $comment->privacy = get_comment_meta( $comment_id, '_privacy', true );

        $files = array();
        if ( $files_meta != '' ) {
            foreach ($files_meta as $index => $attachment_id) {
                $temp = $this->get_file( $attachment_id );

                if ( $temp ) {
                    $files[] = $temp;
                } else {
                    //delete the file from meta. may be it's deleted
                    unset( $files_meta[$index] );
                    update_comment_meta( $comment_id, '_files', $files_meta );
                }
            }
        }

        $comment->files = $files;

        return $comment;
    }

    /**
     * Get all comments for a post type
     *
     * @param int $post_id
     * @param string $order
     * @return object
     */
    function get_comments( $post_id, $order = 'ASC' ) {
        $comments = get_comments( array('post_id' => $post_id, 'order' => $order) );

        //prepare comment attachments
        if ( $comments ) {
            foreach ($comments as $key => $comment) {
                $files = get_comment_meta( $comment->comment_ID, '_files', true );

                if ( $files != '' ) {
                    $file_array = array();

                    foreach ($files as $attachment_id) {
                        $file = $this->get_file( $attachment_id );

                        if ( $file ) {
                            $file_array[] = $file;
                        }
                    }

                    if ( $file_array ) {
                        $comments[$key]->files = $file_array;
                    }
                }
            }
        }

        return $comments;
    }

    /**
     * Upload a file and insert as attachment
     *
     * @param int $post_id
     * @return int|bool
     */
    function upload_file( $post_id = 0 ) {
        if ( $_FILES['cpm_attachment']['error'] > 0 ) {
            return false;
        }

        $upload = array(
            'name' => $_FILES['cpm_attachment']['name'],
            'type' => $_FILES['cpm_attachment']['type'],
            'tmp_name' => $_FILES['cpm_attachment']['tmp_name'],
            'error' => $_FILES['cpm_attachment']['error'],
            'size' => $_FILES['cpm_attachment']['size']
        );

        $uploaded_file = wp_handle_upload( $upload, array('test_form' => false) );

        if ( isset( $uploaded_file['file'] ) ) {
            $file_loc = $uploaded_file['file'];
            $file_name = basename( $_FILES['cpm_attachment']['name'] );
            $file_type = wp_check_filetype( $file_name );

            $attachment = array(
                'post_mime_type' => $file_type['type'],
                'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $attach_id = wp_insert_attachment( $attachment, $file_loc );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            return $attach_id;
        }

        return false;
    }

    /**
     * Get an attachment file
     *
     * @param int $attachment_id
     * @return array
     */
    function get_file( $attachment_id ) {
        $file = get_post( $attachment_id );

        if ( $file ) {
            $response = array(
                'id' => $attachment_id,
                'name' => get_the_title( $attachment_id ),
                'url' => wp_get_attachment_url( $attachment_id ),
            );

            if ( wp_attachment_is_image( $attachment_id ) ) {

                $thumb = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
                $response['thumb'] = $thumb[0];
            } else {
                $response['thumb'] = wp_mime_type_icon( $file->post_mime_type );
            }

            return $response;
        }

        return false;
    }

    /**
     * Get the attachments of a post
     *
     * @param int $post_id
     * @return array attachment list
     */
    function get_attachments( $post_id ) {
        $att_list = array();

        $args = array(
            'post_type' => 'attachment',
            'numberposts' => -1,
            'post_status' => null,
            'post_parent' => $post_id,
            'order' => 'ASC',
            'orderby' => 'menu_order'
        );

        $attachments = get_posts( $args );

        foreach ($attachments as $attachment) {
            $att_list[$attachment->ID] = array(
                'id' => $attachment->ID,
                'name' => $attachment->post_title,
                'url' => wp_get_attachment_url( $attachment->ID ),
            );

            if ( wp_attachment_is_image( $attachment->ID ) ) {

                $thumb = wp_get_attachment_image_src( $attachment->ID, 'thumbnail' );
                $att_list[$attachment->ID]['thumb'] = $thumb[0];
            } else {
                $att_list[$attachment->ID]['thumb'] = wp_mime_type_icon( $file->post_mime_type );
            }
        }

        return $att_list;
    }

    function associate_file( $file_id, $parent_id = 0 ) {
        $update = wp_update_post( array(
            'ID' => $file_id,
            'post_parent' => $parent_id
                ) );
    }

    function delete_file( $file_id, $force = false ) {
        wp_delete_attachment( $file_id, $force );
    }

}