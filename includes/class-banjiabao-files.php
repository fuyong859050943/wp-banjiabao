<?php
/**
 * 文件处理类
 */

class BanJiaBao_Files {
    
    /**
     * 打包网站文件
     */
    public function pack_files($exclude_patterns = array()) {
        $upload_dir = wp_upload_dir();
        $site_root = ABSPATH;
        
        // 默认排除的文件/目录
        $default_excludes = array(
            'wp-content/cache',
            'wp-content/backup',
            'wp-content/uploads/wp-banjiabao',
            'wp-config.php',
            '.git',
            '.svn',
            'node_modules',
            'wp-json'
        );
        
        $exclude_patterns = array_merge($default_excludes, $exclude_patterns);
        
        $files_list = $this->get_file_list($site_root, $site_root, $exclude_patterns);
        
        return $files_list;
    }
    
    /**
     * 获取文件列表
     */
    private function get_file_list($path, $base_path, $exclude_patterns) {
        $files = array();
        
        if (!is_dir($path)) {
            return $files;
        }
        
        $handle = opendir($path);
        
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $full_path = $path . '/' . $file;
            $relative_path = str_replace($base_path . '/', '', $full_path);
            
            // 检查是否应该排除
            if ($this->should_exclude($relative_path, $exclude_patterns)) {
                continue;
            }
            
            if (is_dir($full_path)) {
                $files = array_merge($files, $this->get_file_list($full_path, $base_path, $exclude_patterns));
            } else {
                $files[] = $relative_path;
            }
        }
        
        closedir($handle);
        return $files;
    }
    
    /**
     * 检查是否应该排除
     */
    private function should_exclude($path, $exclude_patterns) {
        foreach ($exclude_patterns as $pattern) {
            if (strpos($path, $pattern) === 0) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 压缩文件到 ZIP
     */
    public function create_zip($files, $zip_path) {
        if (!class_exists('ZipArchive')) {
            return new WP_Error('zip_error', 'ZipArchive 不可用');
        }
        
        $zip = new ZipArchive();
        $result = $zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        
        if ($result !== true) {
            return new WP_Error('zip_error', '无法创建 ZIP 文件，错误代码: ' . $result);
        }
        
        foreach ($files as $file) {
            $full_path = ABSPATH . $file;
            if (file_exists($full_path) && is_file($full_path)) {
                $zip->addFile($full_path, $file);
            }
        }
        
        $zip->close();
        
        return $zip_path;
    }
    
    /**
     * 解压 ZIP 文件
     */
    public function extract_zip($zip_path, $destination) {
        if (!class_exists('ZipArchive')) {
            return new WP_Error('zip_error', 'ZipArchive 不可用');
        }
        
        $zip = new ZipArchive();
        $result = $zip->open($zip_path);
        
        if ($result !== true) {
            return new WP_Error('zip_error', '无法打开 ZIP 文件，错误代码: ' . $result);
        }
        
        $zip->extractTo($destination);
        $zip->close();
        
        return true;
    }
    
    /**
     * 获取目录大小
     */
    public function get_directory_size($path) {
        $size = 0;
        
        if (!is_dir($path)) {
            return $size;
        }
        
        $handle = opendir($path);
        
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $full_path = $path . '/' . $file;
            
            if (is_dir($full_path)) {
                $size += $this->get_directory_size($full_path);
            } else {
                $size += filesize($full_path);
            }
        }
        
        closedir($handle);
        return $size;
    }
    
    /**
     * 复制目录
     */
    public function copy_directory($source, $destination) {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $handle = opendir($source);
        
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $src_file = $source . '/' . $file;
            $dst_file = $destination . '/' . $file;
            
            if (is_dir($src_file)) {
                $this->copy_directory($src_file, $dst_file);
            } else {
                copy($src_file, $dst_file);
            }
        }
        
        closedir($handle);
        return true;
    }
    
    /**
     * 删除目录
     */
    public function delete_directory($path) {
        if (!is_dir($path)) {
            return false;
        }
        
        $handle = opendir($path);
        
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $full_path = $path . '/' . $file;
            
            if (is_dir($full_path)) {
                $this->delete_directory($full_path);
            } else {
                unlink($full_path);
            }
        }
        
        closedir($handle);
        rmdir($path);
        return true;
    }
    
    /**
     * 格式化文件大小
     */
    public function format_size($bytes) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}