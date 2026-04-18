<div class="wrap bjbao-wrap">
    <h1>云存储设置</h1>
    
    <div class="bjbao-cloud-intro">
        <p>将备份上传到云端，更安全、更可靠。支持阿里云 OSS 和腾讯云 COS。</p>
    </div>
    
    <div class="bjbao-tabs">
        <button class="bjbao-tab active" data-tab="aliyun">
            <span class="dashicons dashicons-cloud"></span>
            阿里云 OSS
        </button>
        <button class="bjbao-tab" data-tab="tencent">
            <span class="dashicons dashicons-cloud"></span>
            腾讯云 COS
        </button>
    </div>
    
    <form id="bjbao-cloud-form">
        <!-- 阿里云 OSS -->
        <div class="bjbao-tab-content active" id="tab-aliyun">
            <div class="bjbao-panel">
                <h2>阿里云 OSS 配置</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">AccessKey ID</th>
                        <td>
                            <input type="text" name="aliyun[access_key]" 
                                   value="<?php echo esc_attr($cloud_config['aliyun']['access_key'] ?? ''); ?>"
                                   class="regular-text">
                            <p class="description">前往 <a href="https://ram.console.aliyun.com/" target="_blank">阿里云 RAM 控制台</a> 获取 AccessKey</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">AccessKey Secret</th>
                        <td>
                            <input type="password" name="aliyun[secret_key]" 
                                   value="<?php echo esc_attr($cloud_config['aliyun']['secret_key'] ?? ''); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">EndPoint</th>
                        <td>
                            <input type="text" name="aliyun[endpoint]" 
                                   value="<?php echo esc_attr($cloud_config['aliyun']['endpoint'] ?? ''); ?>"
                                   placeholder="oss-cn-hangzhou.aliyuncs.com"
                                   class="regular-text">
                            <p class="description">填写你的 OSS 地域节点，如：oss-cn-hangzhou.aliyuncs.com</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Bucket 名称</th>
                        <td>
                            <input type="text" name="aliyun[bucket]" 
                                   value="<?php echo esc_attr($cloud_config['aliyun']['bucket'] ?? ''); ?>"
                                   class="regular-text">
                            <p class="description">你的 OSS Bucket 名称</p>
                        </td>
                    </tr>
                </table>
                
                <button type="button" class="button bjbao-btn-test" data-provider="aliyun">
                    测试连接
                </button>
            </div>
        </div>
        
        <!-- 腾讯云 COS -->
        <div class="bjbao-tab-content" id="tab-tencent">
            <div class="bjbao-panel">
                <h2>腾讯云 COS 配置</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">SecretId</th>
                        <td>
                            <input type="text" name="tencent[secret_id]" 
                                   value="<?php echo esc_attr($cloud_config['tencent']['secret_id'] ?? ''); ?>"
                                   class="regular-text">
                            <p class="description">前往 <a href="https://console.cloud.tencent.com/cam/capi" target="_blank">腾讯云密钥管理</a> 获取 SecretId</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">SecretKey</th>
                        <td>
                            <input type="password" name="tencent[secret_key]" 
                                   value="<?php echo esc_attr($cloud_config['tencent']['secret_key'] ?? ''); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">地域 (Region)</th>
                        <td>
                            <input type="text" name="tencent[region]" 
                                   value="<?php echo esc_attr($cloud_config['tencent']['region'] ?? ''); ?>"
                                   placeholder="ap-guangzhou"
                                   class="regular-text">
                            <p class="description">COS Bucket 地域，如：ap-guangzhou（广州）、ap-shanghai（上海）</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Bucket 名称</th>
                        <td>
                            <input type="text" name="tencent[bucket]" 
                                   value="<?php echo esc_attr($cloud_config['tencent']['bucket'] ?? ''); ?>"
                                   class="regular-text">
                            <p class="description">格式：your-bucket-name-1251234567</p>
                        </td>
                    </tr>
                </table>
                
                <button type="button" class="button bjbao-btn-test" data-provider="tencent">
                    测试连接
                </button>
            </div>
        </div>
        
        <p class="submit">
            <button type="submit" class="button button-primary">保存配置</button>
        </p>
    </form>
    
    <div class="bjbao-result" id="bjbao-cloud-result" style="display: none;"></div>
</div>

<style>
.bjbao-wrap { max-width: 800px; }
.bjbao-cloud-intro {
    background: #f0f6fc;
    padding: 15px 20px;
    border-radius: 5px;
    border-left: 4px solid #3498db;
    margin: 20px 0;
}
.bjbao-cloud-intro p { margin: 0; }
.bjbao-tabs {
    display: flex;
    gap: 5px;
    margin: 20px 0;
    border-bottom: 2px solid #f0f0f0;
}
.bjbao-tab {
    background: none;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 14px;
    color: #666;
    display: flex;
    align-items: center;
    gap: 5px;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
}
.bjbao-tab:hover { color: #2271b1; }
.bjbao-tab.active {
    color: #2271b1;
    border-bottom-color: #2271b1;
}
.bjbao-tab-content { display: none; }
.bjbao-tab-content.active { display: block; }
.bjbao-panel {
    background: white;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 25px;
}
.bjbao-panel h2 { margin-top: 0; }
.bjbao-btn-test { margin-top: 15px; }
.bjbao-result {
    margin-top: 20px;
    padding: 15px;
    border-radius: 5px;
}
.bjbao-result.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}
.bjbao-result.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tab 切换
    $('.bjbao-tab').on('click', function() {
        var tab = $(this).data('tab');
        
        $('.bjbao-tab').removeClass('active');
        $(this).addClass('active');
        
        $('.bjbao-tab-content').removeClass('active');
        $('#tab-' + tab).addClass('active');
    });
    
    // 保存配置
    $('#bjbao-cloud-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        
        $btn.prop('disabled', true).text('保存中...');
        
        var aliyun = {};
        var tencent = {};
        
        $form.find('[name^="aliyun"]').each(function() {
            var name = $(this).attr('name').replace('aliyun[', '').replace(']', '');
            aliyun[name] = $(this).val();
        });
        
        $form.find('[name^="tencent"]').each(function() {
            var name = $(this).attr('name').replace('tencent[', '').replace(']', '');
            tencent[name] = $(this).val();
        });
        
        $.ajax({
            url: bjbao.ajax_url,
            type: 'POST',
            data: {
                action: 'banjiabao_save_settings',
                nonce: bjbao.nonce,
                cloud_config: {
                    aliyun: aliyun,
                    tencent: tencent
                }
            },
            success: function(res) {
                if (res.success) {
                    $('#bjbao-cloud-result')
                        .removeClass('error')
                        .addClass('success')
                        .html('<strong>✓ 保存成功！</strong> 云存储配置已保存。')
                        .show();
                } else {
                    $('#bjbao-cloud-result')
                        .removeClass('success')
                        .addClass('error')
                        .html('<strong>✗ 保存失败：</strong>' + res.data)
                        .show();
                }
                $btn.prop('disabled', false).text('保存配置');
            }
        });
    });
    
    // 测试连接
    $('.bjbao-btn-test').on('click', function() {
        var $btn = $(this);
        var provider = $btn.data('provider');
        var $form = $('#bjbao-cloud-form');
        
        $btn.prop('disabled', true).text('测试中...');
        
        var config = {};
        
        if (provider === 'aliyun') {
            $form.find('[name^="aliyun"]').each(function() {
                var name = $(this).attr('name').replace('aliyun[', '').replace(']', '');
                config[name] = $(this).val();
            });
        } else {
            $form.find('[name^="tencent"]').each(function() {
                var name = $(this).attr('name').replace('tencent[', '').replace(']', '');
                config[name] = $(this).val();
            });
        }
        
        $.ajax({
            url: bjbao.ajax_url,
            type: 'POST',
            data: {
                action: 'banjiabao_test_cloud',
                nonce: bjbao.nonce,
                provider: provider,
                config: config
            },
            success: function(res) {
                if (res.success) {
                    $('#bjbao-cloud-result')
                        .removeClass('error')
                        .addClass('success')
                        .html('<strong>✓ 连接成功！</strong> 可以正常使用云存储功能。')
                        .show();
                } else {
                    $('#bjbao-cloud-result')
                        .removeClass('success')
                        .addClass('error')
                        .html('<strong>✗ 连接失败：</strong>' + res.data)
                        .show();
                }
                $btn.prop('disabled', false).text('测试连接');
            }
        });
    });
});
</script>