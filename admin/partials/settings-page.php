<div class="wrap bjbao-wrap">
    <h1>插件设置</h1>
    
    <form id="bjbao-settings-form">
        <div class="bjbao-panel">
            <h2>通用设置</h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">备份文件保存位置</th>
                    <td>
                        <code><?php echo WP_BANJIABAO_UPLOAD_DIR; ?></code>
                        <p class="description">插件会在这个目录下创建备份文件</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">备份文件保留数量</th>
                    <td>
                        <input type="number" name="settings[max_backups]" 
                               value="<?php echo esc_attr($settings['max_backups'] ?? 10); ?>"
                               min="1" max="100" class="small-text">
                        <p class="description">超过这个数量会删除旧的备份（设为 0 则不自动删除）</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="bjbao-panel">
            <h2>自动备份</h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">启用自动备份</th>
                    <td>
                        <label>
                            <input type="checkbox" name="settings[auto_backup]" value="1"
                                   <?php checked($settings['auto_backup'] ?? false, true); ?>>
                            启用定时自动备份
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">备份频率</th>
                    <td>
                        <select name="settings[auto_backup_interval]">
                            <option value="hourly" <?php selected($settings['auto_backup_interval'] ?? '', 'hourly'); ?>>每小时</option>
                            <option value="twicedaily" <?php selected($settings['auto_backup_interval'] ?? '', 'twicedaily'); ?>>每天两次</option>
                            <option value="daily" <?php selected($settings['auto_backup_interval'] ?? 'daily', 'daily'); ?>>每天</option>
                            <option value="weekly" <?php selected($settings['auto_backup_interval'] ?? '', 'weekly'); ?>>每周</option>
                            <option value="monthly" <?php selected($settings['auto_backup_interval'] ?? '', 'monthly'); ?>>每月</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">自动备份类型</th>
                    <td>
                        <select name="settings[auto_backup_type]">
                            <option value="full" <?php selected($settings['auto_backup_type'] ?? 'full', 'full'); ?>>完整备份</option>
                            <option value="database" <?php selected($settings['auto_backup_type'] ?? '', 'database'); ?>>仅数据库</option>
                        </select>
                    </td>
                </tr>
                <?php if ($next_backup) : ?>
                <tr>
                    <th scope="row">下次备份时间</th>
                    <td>
                        <strong><?php echo date('Y-m-d H:i:s', $next_backup); ?></strong>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <div class="bjbao-panel">
            <h2>高级设置</h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">文件大小限制</th>
                    <td>
                        <input type="number" name="settings[max_file_size]" 
                               value="<?php echo esc_attr($settings['max_file_size'] ?? 0); ?>"
                               min="0" class="small-text"> MB
                        <p class="description">设为 0 则使用服务器限制（php.ini 的 upload_max_filesize）</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">排除的文件/目录</th>
                    <td>
                        <textarea name="settings[exclude_patterns]" rows="5" class="large-text"><?php 
                            echo esc_textarea($settings['exclude_patterns'] ?? "wp-content/cache\nwp-content/backup\nwp-content/uploads/wp-banjiabao\n.git\n.svn");
                        ?></textarea>
                        <p class="description">每行一个，备份时将排除这些文件/目录</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <p class="submit">
            <button type="submit" class="button button-primary">保存设置</button>
        </p>
    </form>
    
    <div class="bjbao-result" id="bjbao-settings-result" style="display: none;"></div>
    
    <div class="bjbao-panel bjbao-danger-zone">
        <h2>危险区域</h2>
        <p>以下操作不可逆，请谨慎操作！</p>
        
        <form id="bjbao-clear-form">
            <button type="submit" class="button button-link-delete">
                清空所有本地备份文件
            </button>
        </form>
    </div>
</div>

<style>
.bjbao-wrap { max-width: 800px; }
.bjbao-panel {
    background: white;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 25px;
    margin: 20px 0;
}
.bjbao-panel h2 { margin-top: 0; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; }
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
.bjbao-danger-zone {
    border-color: #f5c6cb;
}
.bjbao-danger-zone h2 { color: #721c24; }
</style>

<script>
jQuery(document).ready(function($) {
    // 保存设置
    $('#bjbao-settings-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        
        $btn.prop('disabled', true).text('保存中...');
        
        var settings = {};
        
        $form.find('[name^="settings"]').each(function() {
            var name = $(this).attr('name').replace('settings[', '').replace(']', '');
            
            if ($(this).attr('type') === 'checkbox') {
                settings[name] = $(this).is(':checked') ? 1 : 0;
            } else {
                settings[name] = $(this).val();
            }
        });
        
        $.ajax({
            url: bjbao.ajax_url,
            type: 'POST',
            data: {
                action: 'banjiabao_save_settings',
                nonce: bjbao.nonce,
                settings: settings
            },
            success: function(res) {
                if (res.success) {
                    $('#bjbao-settings-result')
                        .removeClass('error')
                        .addClass('success')
                        .html('<strong>✓ 设置已保存！</strong>')
                        .show()
                        .delay(3000)
                        .fadeOut();
                } else {
                    $('#bjbao-settings-result')
                        .removeClass('success')
                        .addClass('error')
                        .html('<strong>✗ 保存失败：</strong>' + res.data)
                        .show();
                }
                $btn.prop('disabled', false).text('保存设置');
            }
        });
    });
    
    // 清空备份
    $('#bjbao-clear-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!confirm('确定要清空所有本地备份吗？此操作不可恢复！')) {
            return;
        }
        
        alert('此功能即将开放。');
    });
});
</script>