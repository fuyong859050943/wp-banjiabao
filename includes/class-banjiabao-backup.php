<?php
/**
 * 备份功能类
 */

class BanJiaBao_Backup {
    
    private $files;
    private $database;
    private $cloud;
    
    public function __construct() {
        $this->files = new BanJiaBao_Files();
        $this->database = new BanJiaBao_Database();
    }
    
    /**
     * 创建备份
     */
    public function create_backup($options = array()) {
        $defaults = array(
            'type' => 'full', // full, files, database
            'storage' => 'local', // local, aliyun, tencent
            'name' => '',
        );
        
        $options = array_merge($defaults, $options);
        
        $upload_dir = WP_BANJIABAO_UPLOAD_DIR;
        $timestamp = date('Y-m-d-His');
        $backup_name = $options['name'] ? $options['name'] : 'backup-' . $timestamp;
        
        $backup_dir = $upload_dir . 'backups/' . $backup_name . '/';
        
        if (!file_exists($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        $result = array(
            'name' => $backup_name,
            'timestamp' => $timestamp,
            'type' => $options['type'],
            'files' => '',
            'database' => '',
            'manifest' => array(),
        );
        
        // 备份文件
        if ($options['type'] === 'full' || $options['type'] === 'files') {
            $files = $this->files->pack_files();
            $zip_path = $backup_dir . 'files.zip';
            
            $zip_result = $this->files->create_zip($files, $zip_path);
            
            if (is_wp_error($zip_result)) {
                return $zip_result;
            }
            
            $result['files'] = $zip_path;
            $result['manifest']['files_size'] = filesize($zip_path);
        }
        
        // 备份数据库
        if ($options['type'] === 'full' || $options['type'] === 'database') {
            $db_path = $backup_dir . 'database.sql';
            
            $db_result = $this->database->backup($db_path);
            
            if (is_wp_error($db_result)) {
                return $db_result;
            }
            
            $result['database'] = $db_path;
            $result['manifest']['database_size'] = filesize($db_path);
        }
        
        // 创建清单
        $result['manifest']['version'] = WP_BANJIABAO_VERSION;
        $result['manifest']['backup_time'] = current_time('mysql');
        $result['manifest']['site_url'] = get_site_url();
        $result['manifest']['wp_version'] = get_bloginfo('version');
        
        file_put_contents(
            $backup_dir . 'manifest.json',
            json_encode($result['manifest'], JSON_UNESCAPED_UNICODE)
        );
        
        // 打包成单个 ZIP
        $final_zip = $upload_dir . 'backups/' . $backup_name . '.zip';
        
        $zip = new ZipArchive();
        $zip->open($final_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        
        if ($result['files'] && file_exists($result['files'])) {
            $zip->addFile($result['files'], 'files.zip');
        }
        
        if ($result['database'] && file_exists($result['database'])) {
            $zip->addFile($result['database'], 'database.sql');
        }
        
        $zip->addFile($backup_dir . 'manifest.json', 'manifest.json');
        $zip->close();
        
        // 清理临时目录
        $this->files->delete_directory($backup_dir);
        
        $result['file'] = $final_zip;
        $result['manifest']['total_size'] = filesize($final_zip);
        
        // 上传到云端（如果选择）
        if ($options['storage'] !== 'local') {
            $cloud_result = $this->upload_to_cloud($final_zip, $backup_name . '.zip', $options['storage']);
            
            if (is_wp_error($cloud_result)) {
                return $cloud_result;
            }
            
            $result['cloud'] = $cloud_result;
        }
        
        // 记录到数据库
        $this->save_backup_record($result);
        
        return $result;
    }
    
    /**
     * 恢复备份
     */
    public function restore_backup($backup_file, $options = array()) {
        $defaults = array(
            'restore_files' => true,
            'restore_database' => true,
            'replace_url' => true,
            'old_url' => '',
            'new_url' => '',
        );
        
        $options = array_merge($defaults, $options);
        
        if (!file_exists($backup_file)) {
            return new WP_Error('restore_error', '备份文件不存在');
        }
        
        // 解压到临时目录
        $temp_dir = WP_BANJIABAO_UPLOAD_DIR . 'restore/' . uniqid() . '/';
        
        if (!file_exists($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }
        
        $extract_result = $this->files->extract_zip($backup_file, $temp_dir);
        
        if (is_wp_error($extract_result)) {
            return $extract_result;
        }
        
        $result = array();
        
        // 恢复数据库
        if ($options['restore_database'] && file_exists($temp_dir . 'database.sql')) {
            // 检查 URL 替换
            if ($options['replace_url'] && !empty($options['old_url']) && !empty($options['new_url'])) {
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
        
        // 恢复文件
        if ($options['restore_files'] && file_exists($temp_dir . 'files.zip')) {
            $files_result = $this->files->extract_zip($temp_dir . 'files.zip', ABSPATH);
            
            if (is_wp_error($files_result)) {
                return $files_result;
            }
            
            $result['files'] = true;
        }
        
        // 更新 URL
        if ($options['replace_url'] && !empty($options['new_url'])) {
            $this->database->replace_url($options['old_url'], $options['new_url']);
            $result['url_updated'] = true;
        }
        
        // 清理
        $this->files->delete_directory($temp_dir);
        
        // 清除缓存
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        return $result;
    }
    
    /**
     * 上传到云端
     */
    private function upload_to_cloud($local_file, $remote_path, $provider) {
        $config = get_option('wp_banjiabao_cloud_config', array());
        
        if (empty($config[$provider])) {
            return new WP_Error('config_error', '云存储配置不存在');
        }
        
        $cloud = new BanJiaBao_Cloud($provider, $config[$provider]);
        
        return $cloud->upload($local_file, 'wp-banjiabao/' . $remote_path);
    }
    
    /**
     * 从云端下载备份
     */
    public function download_from_cloud($remote_path, $provider) {
        $config = get_option('wp_banjiabao_cloud_config', array());
        
        if (empty($config[$provider])) {
            return new WP_Error('config_error', '云存储配置不存在');
        }
        
        $cloud = new BanJiaBao_Cloud($provider, $config[$provider]);
        
        $local_file = WP_BANJIABAO_UPLOAD_DIR . 'downloads/' . basename($remote_path);
        
        if (!file_exists(dirname($local_file))) {
            mkdir(dirname($local_file), 0755, true);
        }
        
        return $cloud->download($remote_path, $local_file);
    }
    
    /**
     * 获取备份列表
     */
    public function get_backup_list() {
        $upload_dir = WP_BANJIABAO_UPLOAD_DIR . 'backups/';
        
        if (!file_exists($upload_dir)) {
            return array();
        }
        
        $backups = array();
        $files = glob($upload_dir . '*.zip');
        
        foreach ($files as $file) {
            $filename = basename($file);
            
            // 跳过目录
            if (is_dir($file)) {
                continue;
            }
            
            $backups[] = array(
                'file' => $file,
                'name' => str_replace('.zip', '', $filename),
                'size' => filesize($file),
                'size_formatted' => $this->files->format_size(filesize($file)),
                'date' => filemtime($file),
                'date_formatted' => date('Y-m-d H:i:s', filemtime($file)),
            );
        }
        
        // 按日期排序
        usort($backups, function($a, $b) {
            return $b['date'] - $a['date'];
        });
        
        return $backups;
    }
    
    /**
     * 删除备份
     */
    public function delete_backup($backup_file) {
        if (file_exists($backup_file)) {
            return unlink($backup_file);
        }
        
        return new WP_Error('delete_error', '备份文件不存在');
    }
    
    /**
     * 保存备份记录
     */
    private function save_backup_record($result) {
        $record = array(
            'name' => $result['name'],
            'type' => $result['type'],
            'file' => $result['file'],
            'size' => $result['manifest']['total_size'],
            'time' => current_time('mysql'),
        );
        
        $records = get_option('wp_banjiabao_backup_records', array());
        $records[] = $record;
        
        // 只保留最近 10 条
        if (count($records) > 10) {
            $records = array_slice($records, -10);
        }
        
        update_option('wp_banjiabao_backup_records', $records);
    }
    
    /**
     * 获取备份记录
     */
    public function get_backup_records() {
        return get_option('wp_banjiabao_backup_records', array());
    }
    
    /**
     * 定时备份任务
     */
    public function schedule_auto_backup($interval = 'daily') {
        if (!wp_next_scheduled('wp_banjiabao_auto_backup')) {
            wp_schedule_event(time(), $interval, 'wp_banjiabao_auto_backup');
        }
    }
    
    /**
     * 取消定时备份
     */
    public function unschedule_auto_backup() {
        if (wp_next_scheduled('wp_banjiabao_auto_backup')) {
            wp_clear_scheduled_event('wp_banjiabao_auto_backup');
        }
    }
}