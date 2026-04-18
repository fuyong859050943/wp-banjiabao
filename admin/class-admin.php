<?php
/**
 * 管理后台菜单
 */

if (!defined('ABSPATH')) {
    exit;
}

// 注册后台菜单
add_action('admin_menu', 'wp_banjiabao_add_admin_menu');

// 加载后台资源
add_action('admin_enqueue_scripts', 'wp_banjiabao_load_admin_assets');

/**
 * 添加后台菜单
 */
function wp_banjiabao_add_admin_menu() {
    // 主菜单
    add_menu_page(
        'WP 搬家宝',
        'WP 搬家宝',
        'manage_options',
        'wp-banjiabao',
        'wp_banjiabao_main_page',
        'dashicons-database-import',
        80
    );
    
    // 子菜单 - 迁移中心
    add_submenu_page(
        'wp-banjiabao',
        '站点迁移',
        '迁移中心',
        'manage_options',
        'wp-banjiabao-migration',
        'wp_banjiabao_migration_page'
    );
    
    // 子菜单 - 备份中心
    add_submenu_page(
        'wp-banjiabao',
        '备份中心',
        '备份中心',
        'manage_options',
        'wp-banjiabao-backup',
        'wp_banjiabao_backup_page'
    );
    
    // 子菜单 - 云存储设置
    add_submenu_page(
        'wp-banjiabao',
        '云存储设置',
        '云存储',
        'manage_options',
        'wp-banjiabao-cloud',
        'wp_banjiabao_cloud_page'
    );
    
    // 子菜单 - 设置
    add_submenu_page(
        'wp-banjiabao',
        '设置',
        '设置',
        'manage_options',
        'wp-banjiabao-settings',
        'wp_banjiabao_settings_page'
    );
}

/**
 * 加载后台资源
 */
function wp_banjiabao_load_admin_assets($hook) {
    // 只在插件页面加载资源
    if (strpos($hook, 'wp-banjiabao') === false) {
        return;
    }
    
    // CSS
    wp_enqueue_style(
        'wp-banjiabao-admin',
        WP_BANJIABAO_URL . 'admin/css/admin.css',
        array(),
        WP_BANJIABAO_VERSION
    );
    
    // JS
    wp_enqueue_script(
        'wp-banjiabao-admin',
        WP_BANJIABAO_URL . 'admin/js/admin.js',
        array('jquery'),
        WP_BANJIABAO_VERSION,
        true
    );
    
    // 本地化
    wp_localize_script('wp-banjiabao-admin', 'bjbao', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp_banjiabao_nonce'),
        'strings' => array(
            'confirm_delete' => '确定要删除这个备份吗？此操作不可恢复。',
            'processing' => '处理中，请稍候...',
            'success' => '操作成功！',
            'error' => '操作失败：',
            'downloading' => '正在下载...',
            'uploading' => '正在上传...',
        ),
    ));
}

// AJAX 处理
add_action('wp_ajax_banjiabao_export', 'wp_banjiabao_ajax_export');
add_action('wp_ajax_banjiabao_import', 'wp_banjiabao_ajax_import');
add_action('wp_ajax_banjiabao_backup', 'wp_banjiabao_ajax_backup');
add_action('wp_ajax_banjiabao_restore', 'wp_banjiabao_ajax_restore');
add_action('wp_ajax_banjiabao_delete_backup', 'wp_banjiabao_ajax_delete_backup');
add_action('wp_ajax_banjiabao_get_preview', 'wp_banjiabao_ajax_get_preview');
add_action('wp_ajax_banjiabao_test_cloud', 'wp_banjiabao_ajax_test_cloud');
add_action('wp_ajax_banjiabao_save_settings', 'wp_banjiabao_ajax_save_settings');

/**
 * AJAX：导出站点
 */
function wp_banjiabao_ajax_export() {
    check_ajax_referer('wp_banjiabao_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
    }
    
    $migration = new BanJiaBao_Migration();
    
    $options = array(
        'include_files' => isset($_POST['include_files']),
        'include_database' => isset($_POST['include_database']),
    );
    
    $result = $migration->export_site($options);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }
    
    wp_send_json_success(array(
        'message' => '导出成功！',
        'file' => $result['final_file'],
        'size' => size_format($result['manifest']['total_size']),
    ));
}

/**
 * AJAX：导入站点
 */
function wp_banjiabao_ajax_import() {
    check_ajax_referer('wp_banjiabao_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
    }
    
    if (empty($_FILES['backup_file'])) {
        wp_send_json_error('请选择备份文件');
    }
    
    $file = $_FILES['backup_file'];
    
    // 检查文件类型
    $allowed_types = array('zip');
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed_types)) {
        wp_send_json_error('只支持 ZIP 格式的文件');
    }
    
    // 移动上传的文件
    $upload_dir = WP_BANJIABAO_UPLOAD_DIR . 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $target_file = $upload_dir . basename($file['name']);
    
    if (!move_uploaded_file($file['tmp_name'], $target_file)) {
        wp_send_json_error('文件上传失败');
    }
    
    // 执行导入
    $migration = new BanJiaBao_Migration();
    
    $options = array(
        'replace_url' => isset($_POST['replace_url']),
        'old_url' => isset($_POST['old_url']) ? $_POST['old_url'] : '',
        'new_url' => isset($_POST['new_url']) ? $_POST['new_url'] : '',
    );
    
    $result = $migration->import_site($target_file, $options);
    
    // 删除上传的临时文件
    @unlink($target_file);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }
    
    wp_send_json_success(array(
        'message' => '导入成功！',
        'details' => $result,
    ));
}

/**
 * AJAX：创建备份
 */
function wp_banjiabao_ajax_backup() {
    check_ajax_referer('wp_banjiabao_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
    }
    
    $backup = new BanJiaBao_Backup();
    
    $options = array(
        'type' => isset($_POST['backup_type']) ? $_POST['backup_type'] : 'full',
        'storage' => isset($_POST['storage']) ? $_POST['storage'] : 'local',
        'name' => isset($_POST['backup_name']) ? sanitize_file_name($_POST['backup_name']) : '',
    );
    
    $result = $backup->create_backup($options);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }
    
    wp_send_json_success(array(
        'message' => '备份创建成功！',
        'file' => $result['file'],
        'size' => size_format($result['manifest']['total_size']),
    ));
}

/**
 * AJAX：恢复备份
 */
function wp_banjiabao_ajax_restore() {
    check_ajax_referer('wp_banjiabao_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
    }
    
    if (empty($_POST['backup_file'])) {
        wp_send_json_error('请选择要恢复的备份');
    }
    
    $backup = new BanJiaBao_Backup();
    
    $options = array(
        'replace_url' => isset($_POST['replace_url']),
        'old_url' => isset($_POST['old_url']) ? $_POST['old_url'] : '',
        'new_url' => isset($_POST['new_url']) ? $_POST['new_url'] : get_site_url(),
    );
    
    $result = $backup->restore_backup($_POST['backup_file'], $options);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }
    
    wp_send_json_success(array(
        'message' => '恢复成功！',
        'details' => $result,
    ));
}

/**
 * AJAX：删除备份
 */
function wp_banjiabao_ajax_delete_backup() {
    check_ajax_referer('wp_banjiabao_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
    }
    
    if (empty($_POST['backup_file'])) {
        wp_send_json_error('请选择要删除的备份');
    }
    
    $backup = new BanJiaBao_Backup();
    $result = $backup->delete_backup($_POST['backup_file']);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }
    
    wp_send_json_success('备份已删除');
}

/**
 * AJAX：获取导出预览
 */
function wp_banjiabao_ajax_get_preview() {
    check_ajax_referer('wp_banjiabao_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
    }
    
    $migration = new BanJiaBao_Migration();
    $preview = $migration->get_export_preview();
    
    wp_send_json_success($preview);
}

/**
 * AJAX：测试云存储连接
 */
function wp_banjiabao_ajax_test_cloud() {
    check_ajax_referer('wp_banjiabao_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
    }
    
    $provider = isset($_POST['provider']) ? $_POST['provider'] : '';
    $config = isset($_POST['config']) ? $_POST['config'] : array();
    
    $cloud = new BanJiaBao_Cloud($provider, $config);
    $result = $cloud->test_connection();
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }
    
    wp_send_json_success('连接成功！');
}

/**
 * AJAX：保存设置
 */
function wp_banjiabao_ajax_save_settings() {
    check_ajax_referer('wp_banjiabao_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
    }
    
    $settings = isset($_POST['settings']) ? $_POST['settings'] : array();
    
    update_option('wp_banjiabao_settings', $settings);
    
    // 保存云存储配置
    if (isset($_POST['cloud_config'])) {
        update_option('wp_banjiabao_cloud_config', $_POST['cloud_config']);
    }
    
    wp_send_json_success('设置已保存');
}

// ==================== 页面模板 ====================

/**
 * 主页面
 */
function wp_banjiabao_main_page() {
    $backup = new BanJiaBao_Backup();
    $backups = $backup->get_backup_list();
    $records = $backup->get_backup_records();
    
    include WP_BANJIABAO_PATH . 'admin/partials/main-page.php';
}

/**
 * 迁移页面
 */
function wp_banjiabao_migration_page() {
    $migration = new BanJiaBao_Migration();
    $preview = $migration->get_export_preview();
    
    include WP_BANJIABAO_PATH . 'admin/partials/migration-page.php';
}

/**
 * 备份页面
 */
function wp_banjiabao_backup_page() {
    $backup = new BanJiaBao_Backup();
    $backups = $backup->get_backup_list();
    
    // 获取云存储配置
    $cloud_config = get_option('wp_banjiabao_cloud_config', array());
    $has_cloud = !empty($cloud_config);
    
    include WP_BANJIABAO_PATH . 'admin/partials/backup-page.php';
}

/**
 * 云存储页面
 */
function wp_banjiabao_cloud_page() {
    $cloud_fields = BanJiaBao_Cloud::get_settings_fields();
    $cloud_config = get_option('wp_banjiabao_cloud_config', array());
    
    include WP_BANJIABAO_PATH . 'admin/partials/cloud-page.php';
}

/**
 * 设置页面
 */
function wp_banjiabao_settings_page() {
    $settings = get_option('wp_banjiabao_settings', array());
    
    // 获取当前定时任务状态
    $next_backup = wp_next_scheduled('wp_banjiabao_auto_backup');
    
    include WP_BANJIABAO_PATH . 'admin/partials/settings-page.php';
}