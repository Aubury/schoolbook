<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! class_exists( 'AWS_License_Helpers' ) ) :

/**
 * Class for plugin help methods
 */
class AWS_License_Helpers {

    /*
     * Generate global notices html
     */
    static public function generate_global_notice_html( $notices ) {

        $current_page_url = function_exists('wc_get_current_admin_url') ? wc_get_current_admin_url() : esc_url( admin_url('admin.php?page=aws-options'));
        $dismiss_link = strpos( $current_page_url, '?' ) === false ? $current_page_url . '?' : $current_page_url . '&';

        $html = '';

        if ( is_array( $notices ) ) {
            foreach( $notices as $notice ) {

                if ( isset( $notice['is_remote'] ) && $notice['is_remote'] ) {
                    $id = isset( $notice['id'] ) ? 'aws_hide_remote_notices=' . sanitize_text_field( $notice['id'] ) : '';
                } else {
                    $id = isset( $notice['id'] ) ? 'aws_hide_global_msg_' . $notice['id'] : '';
                }

                if ( ! isset( $notice['is_remote'] ) && get_option( $id ) ) {
                    continue;
                }

                $buttons_html = '';

                if ( isset( $notice['buttons'] ) && is_array( $notice['buttons'] ) ) {
                    foreach ( $notice['buttons'] as $button_props ) {
                        $button_type = isset( $button_props['secondary'] ) ? 'button-secondary' : 'button-primary';
                        $target = isset( $button_props['target'] ) ? 'target="' . $button_props['target'] . '"' : '';
                        $buttons_html .= '<a href="' . $button_props['link'] . '" ' . $target . ' class="button '. $button_type .'">' . $button_props['text'] . '</a>&nbsp;&nbsp;';
                    }
                }

                $type = isset( $notice['type'] ) ? 'notice-' . $notice['type'] : 'notice-error';
                $bg_color = isset( $notice['type'] ) && $notice['type'] === 'error' ? 'background: rgba(255, 0, 0, 0.03);' : '';

                $image = '';
                if ( isset( $notice['image'] ) && $notice['image'] ) {
                    $image = $notice['image'] === 'logo' ? AWS_PRO_URL . 'assets/img/square-logo.png' : $notice['image'];
                }

                $html .= '<div class="aws-license-notice notice ' . $type . '" style="position:relative;'. $bg_color .'">';

                    if ( $image ) {
                        $html .= '<div style="float:left;margin: 20px 20px 0 0;" class="aws-license-notice--logo">';
                            $html .= '<img style="max-width:70px;border-radius:3px;" src="' . esc_url( $image ) . '">';
                        $html .= '</div>';
                    }

                    $html .= '<div class="aws-license-notice--content">';
                        $html .= '<h2><span style="background: rgba(0, 0, 0, 0.07);padding: 3px 2px;">Advanced Woo Search PRO:</span> ' . $notice['title'] . '</h2>';
                        $html .= '<p style="margin-bottom:15px;">' . $notice['message'] . '</p>';
                        $html .= $buttons_html;
                        $html .= '<div style="margin-bottom:15px;"></div>';
                        if ( ! isset( $notice['hide_dismiss'] ) ) {
                            $html .= '<a href="' . $dismiss_link . $id . '" title="' . __( 'Dismiss', 'advanced-woo-search'  ) . '" style="color:#787c82;text-decoration:none;font-size:16px;position:absolute;top:0;right:1px;border:none;margin:0;padding:9px;background:0 0;cursor:pointer;"><span style="font-size:16px;" class="dashicons dashicons-dismiss"></span></a>';
                        }
                    $html .= '</div>';
                $html .= '</div>';

            }
        }

        echo $html;

    }

}

endif;