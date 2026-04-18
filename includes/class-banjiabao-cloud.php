<?php
/**
 * 云存储类 - 阿里云 OSS 和腾讯云 COS
 */

class BanJiaBao_Cloud {
    
    private $provider;
    private $config;
    
    /**
     * 构造函数
     */
    public function __construct($provider = 'aliyun', $config = array()) {
        $this->provider = $provider;
        $this->config = $config;
    }
    
    /**
     * 上传文件
     */
    public function upload($local_file, $remote_path) {
        switch ($this->provider) {
            case 'aliyun':
                return $this->upload_aliyun($local_file, $remote_path);
            case 'tencent':
                return $this->upload_tencent($local_file, $remote_path);
            default:
                return new WP_Error('provider_error', '不支持的云服务商');
        }
    }
    
    /**
     * 下载文件
     */
    public function download($remote_path, $local_file) {
        switch ($this->provider) {
            case 'aliyun':
                return $this->download_aliyun($remote_path, $local_file);
            case 'tencent':
                return $this->download_tencent($remote_path, $local_file);
            default:
                return new WP_Error('provider_error', '不支持的云服务商');
        }
    }
    
    /**
     * 删除文件
     */
    public function delete($remote_path) {
        switch ($this->provider) {
            case 'aliyun':
                return $this->delete_aliyun($remote_path);
            case 'tencent':
                return $this->delete_tencent($remote_path);
            default:
                return new WP_Error('provider_error', '不支持的云服务商');
        }
    }
    
    /**
     * 列出文件
     */
    public function list_files($prefix = '') {
        switch ($this->provider) {
            case 'aliyun':
                return $this->list_aliyun($prefix);
            case 'tencent':
                return $this->list_tencent($prefix);
            default:
                return new WP_Error('provider_error', '不支持的云服务商');
        }
    }
    
    // ==================== 阿里云 OSS ====================
    
    private function upload_aliyun($local_file, $remote_path) {
        if (!isset($this->config['access_key']) || !isset($this->config['secret_key'])) {
            return new WP_Error('config_error', '阿里云 OSS 配置不完整');
        }
        
        require_once WP_BANJIABAO_PATH . 'vendor/aliyun-oss/autoload.php';
        
        try {
            $oss = new \OSS\OssClient(
                $this->config['access_key'],
                $this->config['secret_key'],
                $this->config['endpoint']
            );
            
            $oss->uploadFile(
                $this->config['bucket'],
                $remote_path,
                $local_file
            );
            
            return true;
        } catch (\OSS\Core\OssException $e) {
            return new WP_Error('upload_error', '阿里云 OSS 上传失败: ' . $e->getMessage());
        }
    }
    
    private function download_aliyun($remote_path, $local_file) {
        if (!isset($this->config['access_key']) || !isset($this->config['secret_key'])) {
            return new WP_Error('config_error', '阿里云 OSS 配置不完整');
        }
        
        require_once WP_BANJIABAO_PATH . 'vendor/aliyun-oss/autoload.php';
        
        try {
            $oss = new \OSS\OssClient(
                $this->config['access_key'],
                $this->config['secret_key'],
                $this->config['endpoint']
            );
            
            $oss->getObject(
                $this->config['bucket'],
                $remote_path,
                $local_file
            );
            
            return true;
        } catch (\OSS\Core\OssException $e) {
            return new WP_Error('download_error', '阿里云 OSS 下载失败: ' . $e->getMessage());
        }
    }
    
    private function delete_aliyun($remote_path) {
        if (!isset($this->config['access_key']) || !isset($this->config['secret_key'])) {
            return new WP_Error('config_error', '阿里云 OSS 配置不完整');
        }
        
        require_once WP_BANJIABAO_PATH . 'vendor/aliyun-oss/autoload.php';
        
        try {
            $oss = new \OSS\OssClient(
                $this->config['access_key'],
                $this->config['secret_key'],
                $this->config['endpoint']
            );
            
            $oss->deleteObject(
                $this->config['bucket'],
                $remote_path
            );
            
            return true;
        } catch (\OSS\Core\OssException $e) {
            return new WP_Error('delete_error', '阿里云 OSS 删除失败: ' . $e->getMessage());
        }
    }
    
    private function list_aliyun($prefix) {
        if (!isset($this->config['access_key']) || !isset($this->config['secret_key'])) {
            return new WP_Error('config_error', '阿里云 OSS 配置不完整');
        }
        
        require_once WP_BANJIABAO_PATH . 'vendor/aliyun-oss/autoload.php';
        
        try {
            $oss = new \OSS\OssClient(
                $this->config['access_key'],
                $this->config['secret_key'],
                $this->config['endpoint']
            );
            
            $options = array(
                'prefix' => $prefix,
            );
            
            $list = $oss->listObjects($this->config['bucket'], $options);
            $objects = $list->getObjectList();
            
            $files = array();
            foreach ($objects as $object) {
                $files[] = array(
                    'name' => $object->getKey(),
                    'size' => $object->getSize(),
                    'time' => $object->getLastModified(),
                );
            }
            
            return $files;
        } catch (\OSS\Core\OssException $e) {
            return new WP_Error('list_error', '阿里云 OSS 列出文件失败: ' . $e->getMessage());
        }
    }
    
    // ==================== 腾讯云 COS ====================
    
    private function upload_tencent($local_file, $remote_path) {
        if (!isset($this->config['secret_id']) || !isset($this->config['secret_key'])) {
            return new WP_Error('config_error', '腾讯云 COS 配置不完整');
        }
        
        require_once WP_BANJIABAO_PATH . 'vendor/tencent-cos/autoload.php';
        
        try {
            $config = array(
                'secretId' => $this->config['secret_id'],
                'secretKey' => $this->config['secret_key'],
                'region' => $this->config['region'],
            );
            
            $cos = new \Qcloud\Cos\Client(array(
                'region' => $this->config['region'],
                'credentials' => array(
                    'secretId' => $this->config['secret_id'],
                    'secretKey' => $this->config['secret_key'],
                ),
            ));
            
            $cos->putObject(array(
                'Bucket' => $this->config['bucket'],
                'Key' => $remote_path,
                'Body' => fopen($local_file, 'rb'),
            ));
            
            return true;
        } catch (\Exception $e) {
            return new WP_Error('upload_error', '腾讯云 COS 上传失败: ' . $e->getMessage());
        }
    }
    
    private function download_tencent($remote_path, $local_file) {
        if (!isset($this->config['secret_id']) || !isset($this->config['secret_key'])) {
            return new WP_Error('config_error', '腾讯云 COS 配置不完整');
        }
        
        require_once WP_BANJIABAO_PATH . 'vendor/tencent-cos/autoload.php';
        
        try {
            $cos = new \Qcloud\Cos\Client(array(
                'region' => $this->config['region'],
                'credentials' => array(
                    'secretId' => $this->config['secret_id'],
                    'secretKey' => $this->config['secret_key'],
                ),
            ));
            
            $result = $cos->getObject(array(
                'Bucket' => $this->config['bucket'],
                'Key' => $remote_path,
                'SaveAs' => $local_file,
            ));
            
            return true;
        } catch (\Exception $e) {
            return new WP_Error('download_error', '腾讯云 COS 下载失败: ' . $e->getMessage());
        }
    }
    
    private function delete_tencent($remote_path) {
        if (!isset($this->config['secret_id']) || !isset($this->config['secret_key'])) {
            return new WP_Error('config_error', '腾讯云 COS 配置不完整');
        }
        
        require_once WP_BANJIABAO_PATH . 'vendor/tencent-cos/autoload.php';
        
        try {
            $cos = new \Qcloud\Cos\Client(array(
                'region' => $this->config['region'],
                'credentials' => array(
                    'secretId' => $this->config['secret_id'],
                    'secretKey' => $this->config['secret_key'],
                ),
            ));
            
            $cos->deleteObject(array(
                'Bucket' => $this->config['bucket'],
                'Key' => $remote_path,
            ));
            
            return true;
        } catch (\Exception $e) {
            return new WP_Error('delete_error', '腾讯云 COS 删除失败: ' . $e->getMessage());
        }
    }
    
    private function list_tencent($prefix) {
        if (!isset($this->config['secret_id']) || !isset($this->config['secret_key'])) {
            return new WP_Error('config_error', '腾讯云 COS 配置不完整');
        }
        
        require_once WP_BANJIABAO_PATH . 'vendor/tencent-cos/autoload.php';
        
        try {
            $cos = new \Qcloud\Cos\Client(array(
                'region' => $this->config['region'],
                'credentials' => array(
                    'secretId' => $this->config['secret_id'],
                    'secretKey' => $this->config['secret_key'],
                ),
            ));
            
            $result = $cos->listObjects(array(
                'Bucket' => $this->config['bucket'],
                'Prefix' => $prefix,
            ));
            
            $files = array();
            foreach ($result['Contents'] as $object) {
                $files[] = array(
                    'name' => $object['Key'],
                    'size' => $object['Size'],
                    'time' => $object['LastModified'],
                );
            }
            
            return $files;
        } catch (\Exception $e) {
            return new WP_Error('list_error', '腾讯云 COS 列出文件失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 测试连接
     */
    public function test_connection() {
        // 简单测试：尝试列出文件
        $result = $this->list_files('test-');
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return true;
    }
    
    /**
     * 获取配置表单
     */
    public static function get_settings_fields() {
        return array(
            'aliyun' => array(
                'name' => '阿里云 OSS',
                'fields' => array(
                    'access_key' => array(
                        'label' => 'AccessKey ID',
                        'type' => 'text',
                    ),
                    'secret_key' => array(
                        'label' => 'AccessKey Secret',
                        'type' => 'password',
                    ),
                    'endpoint' => array(
                        'label' => 'EndPoint',
                        'type' => 'text',
                        'placeholder' => 'oss-cn-hangzhou.aliyuncs.com',
                    ),
                    'bucket' => array(
                        'label' => 'Bucket 名称',
                        'type' => 'text',
                    ),
                ),
            ),
            'tencent' => array(
                'name' => '腾讯云 COS',
                'fields' => array(
                    'secret_id' => array(
                        'label' => 'SecretId',
                        'type' => 'text',
                    ),
                    'secret_key' => array(
                        'label' => 'SecretKey',
                        'type' => 'password',
                    ),
                    'region' => array(
                        'label' => '地域',
                        'type' => 'text',
                        'placeholder' => 'ap-guangzhou',
                    ),
                    'bucket' => array(
                        'label' => 'Bucket 名称',
                        'type' => 'text',
                    ),
                ),
            ),
        );
    }
}