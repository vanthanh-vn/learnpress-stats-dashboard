<?php
/**
 * Plugin Name: LearnPress Custom Addon
 * Description: Notification bar + Course info shortcode + Custom CSS
 * Author: Nguyễn Văn Thanh
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

/* =========================
   1. NOTIFICATION BAR
========================= */
add_action('wp_body_open', 'lp_notification_bar');

function lp_notification_bar() {
    ?>
    <div class="lp-noti-bar">
        <?php
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            echo "Chào " . esc_html($user->display_name) . ", bạn đã sẵn sàng bắt đầu bài học hôm nay chưa?";
        } else {
            echo "Đăng nhập để lưu tiến độ học tập!";
        }
        ?>
    </div>
    <?php
}


/* =========================
   2. SHORTCODE COURSE INFO
========================= */
add_shortcode('lp_course_info', 'lp_course_info_func');

function lp_course_info_func($atts) {

    // Kiểm tra LearnPress
    if (!function_exists('learn_press_get_course')) {
        return "LearnPress chưa được cài!";
    }

    $atts = shortcode_atts([
        'id' => ''
    ], $atts);

    $course_id = intval($atts['id']);
    if (!$course_id) return "Thiếu ID khóa học";

    $course = learn_press_get_course($course_id);
    if (!$course) return "Không tìm thấy khóa học";

    // Số bài học
    $items = $course->get_curriculum_items();
    $lesson_count = is_array($items) ? count($items) : 0;

    // Thời gian
    $duration = $course->get_data('duration');

    // Trạng thái
    $status = "Chưa đăng nhập";

    if (is_user_logged_in()) {
        $user = learn_press_get_current_user();
        $course_data = $user->get_course_data($course_id);

        if ($course_data) {
            if ($course_data->get_status() == 'completed') {
                $status = "Đã hoàn thành";
            } else {
                $status = "Đã đăng ký";
            }
        } else {
            $status = "Chưa đăng ký";
        }
    }

    ob_start();
    ?>
    <div class="lp-course-info">
        <p><b>Số bài học:</b> <?php echo esc_html($lesson_count); ?></p>
        <p><b>Thời gian:</b> <?php echo esc_html($duration); ?></p>
        <p><b>Trạng thái:</b> <?php echo esc_html($status); ?></p>
    </div>
    <?php

    return ob_get_clean();
}


/* =========================
   3. CUSTOM CSS
========================= */
add_action('wp_head', 'lp_custom_style');

function lp_custom_style() {
    ?>
    <style>
        /* Notification bar */
        .lp-noti-bar {
            background: #ff9800;
            color: #fff;
            padding: 10px;
            text-align: center;
            font-weight: bold;
        }

        /* Course info box */
        .lp-course-info {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            background: #f9f9f9;
        }

        /* Nút LearnPress */
        .learn-press .lp-button,
        .learn-press button {
            background-color: #ff5722 !important;
            border: none !important;
            color: white !important;
        }

        .learn-press .lp-button:hover {
            background-color: #e64a19 !important;
        }
    </style>
    <?php
}