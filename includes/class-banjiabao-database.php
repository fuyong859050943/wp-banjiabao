<?php
/**
 * 数据库备份类
 */

class BanJiaBao_Database {
    
    private $db;
    private $charset;
    
    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
    }
    
    /**
     * 备份数据库
     */
    public function backup($output_path) {
        $tables = $this->get_tables();
        
        $content = "-- WP 搬家宝数据库备份\n";
        $content .= "-- 日期: " . date('Y-m-d H:i:s') . "\n";
        $content .= "-- 数据库: " . DB_NAME . "\n\n";
        $content .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        
        foreach ($tables as $table) {
            $content .= $this->dump_table($table);
        }
        
        $content .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        
        // 写入文件
        $result = file_put_contents($output_path, $content);
        
        if ($result === false) {
            return new WP_Error('backup_error', '无法写入数据库备份文件');
        }
        
        return $output_path;
    }
    
    /**
     * 获取所有表
     */
    private function get_tables() {
        $tables = $this->db->get_results("SHOW TABLES", ARRAY_N);
        return array_map(function($table) {
            return $table[0];
        }, $tables);
    }
    
    /**
     * 导出表结构和数据
     */
    private function dump_table($table) {
        $content = "-- --------------------------------------------------------\n";
        $content .= "-- 表结构: {$table}\n";
        $content .= "-- --------------------------------------------------------\n\n";
        
        // 表结构
        $create = $this->db->get_row("SHOW CREATE TABLE {$table}", ARRAY_N);
        $content .= "DROP TABLE IF EXISTS {$table};\n";
        $content .= $create[1] . ";\n\n";
        
        // 表数据
        $rows = $this->db->get_results("SELECT * FROM {$table}", ARRAY_A);
        
        if (!empty($rows)) {
            $content .= "-- 数据: {$table}\n";
            
            foreach ($rows as $row) {
                $values = array();
                
                foreach ($row as $value) {
                    if (is_null($value)) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . $this->db->_real_escape_string($value) . "'";
                    }
                }
                
                $content .= "INSERT INTO {$table} VALUES (" . implode(', ', $values) . ");\n";
            }
            
            $content .= "\n";
        }
        
        return $content;
    }
    
    /**
     * 恢复数据库
     */
    public function restore($sql_file) {
        if (!file_exists($sql_file)) {
            return new WP_Error('restore_error', 'SQL 文件不存在');
        }
        
        $content = file_get_contents($sql_file);
        
        if ($content === false) {
            return new WP_Error('restore_error', '无法读取 SQL 文件');
        }
        
        // 分割 SQL 语句
        $queries = $this->split_sql($content);
        
        foreach ($queries as $query) {
            $query = trim($query);
            
            if (empty($query)) {
                continue;
            }
            
            $result = $this->db->query($query);
            
            if ($result === false) {
                return new WP_Error('restore_error', 'SQL 执行失败: ' . $this->db->last_error);
            }
        }
        
        return true;
    }
    
    /**
     * 分割 SQL 语句
     */
    private function split_sql($sql) {
        // 移除注释
        $sql = preg_replace('/--[^\n]*/', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // 按分号分割
        $statements = array();
        $current = '';
        $in_string = false;
        $string_char = '';
        
        $len = strlen($sql);
        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];
            
            if (($char === '"' || $char === "'") && !$in_string) {
                $in_string = true;
                $string_char = $char;
            } elseif ($char === $string_char && $in_string) {
                $in_string = false;
                $string_char = '';
            }
            
            if ($char === ';' && !$in_string) {
                $statements[] = $current;
                $current = '';
            } else {
                $current .= $char;
            }
        }
        
        if (trim($current)) {
            $statements[] = $current;
        }
        
        return $statements;
    }
    
    /**
     * 替换数据库中的 URL
     */
    public function replace_url($old_url, $new_url) {
        $tables = $this->get_tables();
        
        // 需要替换 URL 的表和列
        $url_fields = array(
            'posts' => array('post_content', 'post_excerpt', 'guid'),
            'postmeta' => array('meta_value'),
            'options' => array('option_value'),
            'comments' => array('comment_content'),
            'terms' => array('description'),
        );
        
        foreach ($tables as $table) {
            // 跳过不包含 URL 的表
            if (!isset($url_fields[$table])) {
                continue;
            }
            
            foreach ($url_fields[$table] as $field) {
                // 检查字段是否存在
                $column_exists = $this->db->get_var("
                    SELECT COUNT(*) 
                    FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
                    AND TABLE_NAME = '{$table}' 
                    AND COLUMN_NAME = '{$field}'
                ");
                
                if (!$column_exists) {
                    continue;
                }
                
                // 替换 URL
                $this->db->query("
                    UPDATE {$table} 
                    SET {$field} = REPLACE({$field}, '{$old_url}', '{$new_url}')
                    WHERE {$field} LIKE '%{$old_url}%'
                ");
            }
        }
        
        // 更新站点 URL
        update_option('siteurl', $new_url);
        update_option('home', $new_url);
        
        return true;
    }
    
    /**
     * 获取数据库大小
     */
    public function get_database_size() {
        $result = $this->db->get_row("
            SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size 
            FROM information_schema.tables 
            WHERE table_schema = '" . DB_NAME . "'
        ", ARRAY_A);
        
        return $result ? $result['size'] : 0;
    }
}