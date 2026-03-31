<?php
/**
 * Plugin Name: LearnPress Stats Dashboard
 * Description: Hiển thị thống kê tổng số khóa học, học viên và tiến độ hoàn thành cho LearnPress.
 * Version: 1.0
 * Author: Hoàng Nam Khánh
 */

// Bảo mật: Ngăn chặn người dùng truy cập trực tiếp vào file này từ trình duyệt
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hàm lấy dữ liệu thống kê từ LearnPress
 */
function lp_stats_get_data() {
    global $wpdb;

    // 1. Lấy tổng số khóa học đã xuất bản
    $total_courses = wp_count_posts('lp_course')->publish;

    // 2. Lấy tổng số học viên (Đếm các ID người dùng duy nhất trong bảng dữ liệu của LearnPress)
    $table_user_items = $wpdb->prefix . 'learnpress_user_items';
    $total_students = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $table_user_items");

    // 3. Lấy số lượng khóa học đã hoàn thành (Trạng thái 'completed')
    $total_completed = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_user_items WHERE item_type = %s AND status = %s",
        'lp_course', 'completed'
    ));

    return array(
        'courses'   => $total_courses ? $total_courses : 0,
        'students'  => $total_students ? $total_students : 0,
        'completed' => $total_completed ? $total_completed : 0
    );
}

/**
 * Thêm Widget vào Dashboard Admin
 */
add_action('wp_dashboard_setup', 'lp_stats_add_dashboard_widget');

function lp_stats_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'lp_stats_widget',         // ID của widget
        'LearnPress Quick Stats',  // Tiêu đề hiển thị
        'lp_stats_display_dashboard_content' // Hàm hiển thị nội dung
    );
}

function lp_stats_display_dashboard_content() {
    $data = lp_stats_get_data();
    echo '<p><strong>Tổng số khóa học:</strong> ' . $data['courses'] . '</p>';
    echo '<p><strong>Tổng số học viên:</strong> ' . $data['students'] . '</p>';
    echo '<p><strong>Khóa học đã hoàn thành:</strong> ' . $data['completed'] . '</p>';
}

/**
 * Tạo Shortcode [lp_total_stats]
 */
add_shortcode('lp_total_stats', 'lp_stats_shortcode_output');

function lp_stats_shortcode_output() {
    $data = lp_stats_get_data();
    
    // Bắt đầu gom nội dung vào biến $output
    $output = '<div class="lp-stats-box" style="border: 2px solid #00a0d2; padding: 15px; border-radius: 8px;">';
    $output .= '<h3 style="margin-top:0;">Thống kê hệ thống</h3>';
    $output .= '<ul>';
    $output .= '<li>Khóa học: ' . $data['courses'] . '</li>';
    $output .= '<li>Học viên: ' . $data['students'] . '</li>';
    $output .= '<li>Hoàn thành: ' . $data['completed'] . '</li>';
    $output .= '</ul>';
    $output .= '</div>';
    
    return $output;
}