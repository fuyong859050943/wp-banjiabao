<?php
/**
 * 迁移功能类
 */

class BanJiaBao_Migration {
    
    private $files;
    private $database;
    
    public function __construct() {
        $this->files = new BanJiaBao_Files();
        $this->database = new BanJiaBao_Database();
    }
    
    /**
     * 导出站点
     */
    public function export_site($options = array()) {
        $defaults = array(
            'include_files' => true,
            'include_database' => true,
            'exclude_themes' => false,
            'exclude_plugins' => false,
            'exclude_uploads' => false,
        );
        
        $options = array_merge($defaults, $options);
        $upload_dir = WP_BANJIABAO_UPLOAD_DIR;
        $timestamp = date('Y-m-d-His');
        $export_dir = $upload_dir . 'exports/' . $timestamp . '/';
        
        // 创建导出目录
        if (!file_exists($export_dir)) {
            mkdir($export_dir, 0755, true);
        }
        
        $result = array(
            'timestamp' => $timestamp,
            'files' => array(),
            'database' => '',
            'manifest' => array(),
        );
        
        // 1. 打包网站文件
        if ($options['include_files']) {
            $exclude_patterns = array();
            
            if ($options['exclude_themes']) {
                $exclude_patterns[] = 'wp-content/themes';
            }
            if ($options['exclude_plugins']) {
                $exclude_patterns[] = 'wp-content/plugins';
            }
            if ($options['exclude_uploads']) {
                $exclude_patterns[] = 'wp-content/uploads';
            }
            
            $files = $this->files->pack_files($exclude_patterns);
            $zip_path = $export_dir . 'files.zip';
            
            $zip_result = $this->files->create_zip($files, $zip_path);
            
            if (is_wp_error($zip_result)) {
                return $zip_result;
            }
            
            $result['files'] = $zip_path;
            $result['manifest']['files_count'] = count($files);
            $result['manifest']['files_size'] = filesize($zip_path);
        }
        
        // 2. 备份数据库
        if ($options['include_database']) {
            $db_path = $export_dir . 'database.sql';
            
            $db_result = $this->database->backup($db_path);
            
            if (is_wp_error($db_result)) {
                return $db_result;
            }
            
            $result['database'] = $db_path;
            $result['manifest']['database_size'] = filesize($db_path);
        }
        
        // 3. 创建清单文件
        $result['manifest']['version'] = WP_BANJIABAO_VERSION;
        $result['manifest']['export_time'] = current_time('mysql');
        $result['manifest']['site_url'] = get_site_url();
        $result['manifest']['wp_version'] = get_bloginfo('version');
        
        file_put_contents(
            $export_dir . 'manifest.json',
            json_encode($result['manifest'], JSON_UNESCAPED_UNICODE)
        );
        
        // 4. 打包成最终导出文件
        $final_zip = $upload_dir . 'exports/wp-backup-' . $timestamp . '.zip';
        
        $zip = new ZipArchive();
        $zip->open($final_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        
        if ($options['include_files'] && file_exists($result['files'])) {
            $zip->addFile($result['files'], 'files.zip');
        }
        
        if ($options['include_database'] && file_exists($result['database'])) {
            $zip->addFile($result['database'], 'database.sql');
        }
        
        $zip->addFile($export_dir . 'manifest.json', 'manifest.json');
        $zip->close();
        
        // 清理临时文件
        $this->files->delete_directory($export_dir);
        
        $result['final_file'] = $final_zip;
        $result['manifest']['total_size'] = filesize($final_zip);
        
        return $result;
    }
    
    /**
     * 导入站点
     */
    public function import_site($zip_file, $options = array()) {
        $defaults = array(
            'import_files' => true,
            'import_database' => true,
            'replace_url' => true,
            'old_url' => '',
            'new_url' => '',
        );
        
        $options = array_merge($defaults, $options);
        
        // 验证文件
        if (!file_exists($zip_file)) {
            return new WP_Error('import_error', '导入文件不存在');
        }
        
        // 解压到临时目录
        $temp_dir = WP_BANJIABAO_UPLOAD_DIR . 'imports/' . uniqid() . '/';
        
        if (!file_exists($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }
        
        $extract_result = $this->files->extract_zip($zip_file, $temp_dir);
        
        if (is_wp_error($extract_result)) {
            return $extract_result;
        }
        
        $result = array();
        
        // 1. 导入数据库
        if ($options['import_database'] && file_exists($temp_dir . 'database.sql')) {
            // 检查是否需要替换 URL
            if ($options['replace_url'] && !empty($options['old_url']) && !empty($options['new_url'])) {
                // 先替换 SQL 文件中的 URL
                $sql_content = file_get_contents($temp_dir . 'database.sql');
                $sql_content = str_replace($options['old_url'], $options['new_url'], $sql_content);
                file_put_contents($temp_dir . 'database.sql', $sql_content);
            }
            
            $db_result = $this->database->restore($temp_dir . 'database.sql');
            
            if (is_wp_error($db_result)) {
                return $db_result;
            }
            
            $result['database'] = true;
        }
        
        // 2. 导入文件
        if ($options['import_files'] && file_exists($temp_dir . 'files.zip')) {
            // 解压 files.zip 到 WordPress 根目录
            $files_zip = $temp_dir . 'files.zip';
            $extract_to = ABSPATH;
            
            $files_result = $this->files->extract_zip($files_zip, $extract_to);
            
            if (is_wp_error($files_result)) {
                return $files_result;
            }
            
            $result['files'] = true;
        }
        
        // 3. 更新 URL（如果需要）
        if ($options['replace_url'] && !empty($options['new_url'])) {
            $this->database->replace_url($options['old_url'], $options['new_url']);
            $result['url_updated'] = true;
        }
        
        // 清理临时文件
        $this->files->delete_directory($temp_dir);
        
        // 清除缓存
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        return $result;
    }
    
    /**
     * 获取导出预览信息
     */
    public function get_export_preview() {
        $info = array();
        
        // 文件大小
        $info['files_size'] = $this->files->get_directory_size(ABSPATH);
        $info['files_size_formatted'] = $this->files->format_size($info['files_size']);
        
        // 数据库大小
        $db = new BanJiaBao_Database();
        $info['database_size'] = $db->get_database_size();
        $info['database_size_formatted'] = $info['database_size'] . ' MB';
        
        // 总大小
        $info['total_size'] = $info['files_size'] + ($info['database_size'] * 1024 * 1024);
        $info['total_size_formatted'] = $this->files->format_size($info['total_size']);
        
        // 文件数量
        $files = $this->files->pack_files();
        $info['files_count'] = count($files);
        
        return $info;
    }
}