<?php
/**
 * Plugin Name: Network Site Stats
 * Description: Thống kê danh sách các site con trong mạng lưới Multisite (Dành cho Super Admin).
 * Version: 1.0
 * Author: Nguyễn Văn Thanh
 * Network: true
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Hook để thêm Menu vào trang Quản trị mạng (Network Admin)
add_action( 'network_admin_menu', 'nss_add_network_menu' );

function nss_add_network_menu() {
    add_menu_page(
        'Thống kê Mạng lưới',     // Tiêu đề thẻ meta
        'Site Stats',             // Tên menu hiển thị
        'manage_network',         // Quyền truy cập (chỉ Super Admin)
        'network-site-stats',     // Slug của URL
        'nss_render_stats_page',  // Hàm hiển thị nội dung
        'dashicons-chart-pie',    // Icon menu
        3                         // Vị trí
    );
}

// 2. Hàm hiển thị giao diện bảng thống kê
function nss_render_stats_page() {
    // Kiểm tra bảo mật quyền Super Admin
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'Bạn không có quyền truy cập trang này.' );
    }
    ?>
    <div class="wrap">
        <h1>Bảng Thống Kê Mạng Lưới (Multisite)</h1>
        <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th style="width: 60px;">ID Site</th>
                    <th>Tên Site (Blog Name)</th>
                    <th>Đường dẫn (URL)</th>
                    <th>Số bài viết (Published)</th>
                    <th>Ngày đăng bài mới nhất</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Hàm get_sites() lấy danh sách toàn bộ các site trong Network
                $sites = get_sites();

                if ( ! empty( $sites ) ) {
                    foreach ( $sites as $site ) {
                        $blog_id = $site->blog_id;

                        // QUAN TRỌNG: Chuyển ngữ cảnh sang Database của site con
                        switch_to_blog( $blog_id );

                        // Lúc này mọi hàm lấy dữ liệu đều tự động nhắm vào site con
                        $site_name  = get_option( 'blogname' );
                        $site_url   = get_site_url();
                        $post_count = wp_count_posts( 'post' )->publish;

                        // Tìm bài viết mới nhất để lấy ngày tháng
                        $latest_posts = get_posts( array(
                            'numberposts' => 1,
                            'post_status' => 'publish'
                        ) );
                        
                        $last_date = 'Chưa có bài viết';
                        if ( ! empty( $latest_posts ) ) {
                            $last_date = date_i18n( get_option( 'date_format' ), strtotime( $latest_posts[0]->post_date ) );
                        }

                        // QUAN TRỌNG: Lấy xong dữ liệu phải trả về ngữ cảnh của trang gốc ban đầu
                        restore_current_blog();

                        // Hiển thị ra các cột HTML
                        echo '<tr>';
                        echo '<td><strong>' . esc_html( $blog_id ) . '</strong></td>';
                        echo '<td>' . esc_html( $site_name ) . '</td>';
                        echo '<td><a href="' . esc_url( $site_url ) . '" target="_blank">' . esc_html( $site_url ) . '</a></td>';
                        echo '<td>' . esc_html( $post_count ? $post_count : 0 ) . '</td>';
                        echo '<td>' . esc_html( $last_date ) . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5">Không tìm thấy trang web nào.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}