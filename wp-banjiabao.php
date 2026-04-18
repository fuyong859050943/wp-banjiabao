/*
Plugin Name: WP 搬家宝
Plugin URI: https://wordpress.org/plugins/wp-banjiabao
Description: 站点迁移 + 云端备份，一站式解决。支持阿里云 OSS、腾讯云 COS。
Version: 1.0.0
Author: xiaxiami
Author URI: https://xiaxiami.com
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-banjiabao
Domain Path: /languages
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
*/

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义常量
define('WP_BANJIABAO_VERSION', '1.0.0');
define('WP_BANJIABAO_PATH', plugin_dir_path(__FILE__));
define('WP_BANJIABAO_URL', plugin_dir_url(__FILE__));
define('WP_BANJIABAO_UPLOAD_DIR', wp_upload_dir()['basedir'] . '/wp-banjiabao/');

// 创建上传目录
if (!file_exists(WP_BANJIABAO_UPLOAD_DIR)) {
    mkdir(WP_BANJIABAO_UPLOAD_DIR, 0755, true);
}

/**
 * 加载核心类
 */
require_once WP_BANJIABAO_PATH . 'includes/class-banjiabao-loader.php';
require_once WP_BANJIABAO_PATH . 'includes/class-banjiabao-database.php';
require_once WP_BANJIABAO_PATH . 'includes/class-banjiabao-files.php';
require_once WP_BANJIABAO_PATH . 'includes/class-banjiabao-cloud.php';
require_once WP_BANJIABAO_PATH . 'includes/class-banjiabao-migration.php';
require_once WP_BANJIABAO_PATH . 'includes/class-banjiabao-backup.php';

/**
 * 初始化插件
 */
function wp_banjiabao_init() {
    // 注册自定义 post type 用于存储备份记录
    register_post_type('bjbackup', array(
        'labels' => array(
            'name' => '备份记录',
            'singular_name' => '备份记录'
        ),
        'public' => false,
        'show_ui' => false,
        'supports' => array('title', 'editor'),
        'menu_icon' => 'dashicons-database',
    ));
}
add_action('init', 'wp_banjiabao_init');

/**
 * 插件激活时调用
 */
function wp_banjiabao_activate() {
    // 创建必要的目录
    if (!file_exists(WP_BANJIABAO_UPLOAD_DIR)) {
        mkdir(WP_BANJIABAO_UPLOAD_DIR, 0755, true);
    }
    
    // 记录插件版本
    update_option('wp_banjiabao_version', WP_BANJIABAO_VERSION);
}
register_activation_hook(__FILE__, 'wp_banjiabao_activate');

/**
 * 插件停用时调用
 */
function wp_banjiabao_deactivate() {
    // 清理定时任务
    wp_clear_scheduled_event('wp_banjiabao_auto_backup');
}
register_deactivation_hook(__FILE__, 'wp_banjiabao_deactivate');