/**
 * 插件加载器类
 */

class BanJiaBao_Loader {
    
    private static $instance = null;
    
    /**
     * 获取单例实例
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 构造函数
     */
    private function __construct() {
        $this->load_hooks();
    }
    
    /**
     * 加载钩子
     */
    private function load_hooks() {
        // 注册 AJAX 处理（已登录用户）
        add_action('wp_ajax_banjiabao_do_backup', array($this, 'ajax_do_backup'));
        
        // 注册定时任务钩子
        add_action('wp_banjiabao_auto_backup', array($this, 'do_auto_backup'));
        
        // 插件升级时清理
        add_action('upgrader_process_complete', array($this, 'on_upgrade'), 10, 2);
    }
    
    /**
     * 执行自动备份
     */
    public function do_auto_backup() {
        $settings = get_option('wp_banjiabao_settings', array());
        $backup_type = $settings['auto_backup_type'] ?? 'full';
        $storage = $settings['auto_backup_storage'] ?? 'local';
        
        $backup = new BanJiaBao_Backup();
        
        $result = $backup->create_backup(array(
            'type' => $backup_type,
            'storage' => $storage,
            'name' => 'auto-' . date('Y-m-d-His'),
        ));
        
        // 记录日志
        if (is_wp_error($result)) {
            error_log('WP搬家宝自动备份失败: ' . $result->get_error_message());
        } else {
            error_log('WP搬家宝自动备份成功: ' . $result['file']);
            
            // 清理旧备份
            $this->cleanup_old_backups();
        }
    }
    
    /**
     * 清理旧备份
     */
    private function cleanup_old_backups() {
        $settings = get_option('wp_banjiabao_settings', array());
        $max_backups = isset($settings['max_backups']) ? intval($settings['max_backups']) : 10;
        
        if ($max_backups <= 0) {
            return;
        }
        
        $backup = new BanJiaBao_Backup();
        $backups = $backup->get_backup_list();
        
        // 跳过自动备份名称的，只清理本地的
        $local_backups = array_filter($backups, function($b) {
            return strpos($b['name'], 'auto-') === 0 || strpos($b['name'], 'backup-') === 0;
        });
        
        if (count($local_backups) > $max_backups) {
            // 删除超出数量的旧备份
            $to_delete = array_slice($local_backups, 0, count($local_backups) - $max_backups);
            
            foreach ($to_delete as $old_backup) {
                $backup->delete_backup($old_backup['file']);
            }
        }
    }
    
    /**
     * 插件升级回调
     */
    public function on_upgrade($upgrader, $options) {
        if ($options['action'] === 'update' && $options['type'] === 'plugin') {
            foreach ($options['plugins'] as $plugin) {
                if ($plugin === plugin_basename(WP_BANJIABAO_PATH . 'wp-banjiabao.php')) {
                    // 升级后清理缓存
                    if (function_exists('wp_cache_flush')) {
                        wp_cache_flush();
                    }
                    
                    // 记录升级日志
                    error_log('WP搬家宝已升级到新版本');
                }
            }
        }
    }
    
    /**
     * 获取插件信息
     */
    public static function get_plugin_info() {
        return array(
            'version' => WP_BANJIABAO_VERSION,
            'name' => 'WP 搬家宝',
            'description' => '站点迁移 + 云端备份，一站式解决',
            'author' => 'Your Name',
            'author_uri' => 'https://example.com',
            'plugin_uri' => 'https://example.com/wp-banjiabao',
        );
    }
    
    /**
     * 检查系统要求
     */
    public static function check_requirements() {
        $errors = array();
        
        // PHP 版本
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $errors[] = '需要 PHP 7.4 或更高版本';
        }
        
        // WordPress 版本
        global $wp_version;
        if (version_compare($wp_version, '5.0', '<')) {
            $errors[] = '需要 WordPress 5.0 或更高版本';
        }
        
        // ZipArchive
        if (!class_exists('ZipArchive')) {
            $errors[] = '需要 PHP ZipArchive 扩展';
        }
        
        // 目录权限
        if (!is_dir(WP_BANJIABAO_UPLOAD_DIR) || !is_writable(WP_BANJIABAO_UPLOAD_DIR)) {
            if (!@mkdir(WP_BANJIABAO_UPLOAD_DIR, 0755, true)) {
                $errors[] = '无法创建上传目录：' . WP_BANJIABAO_UPLOAD_DIR;
            }
        }
        
        return $errors;
    }
}