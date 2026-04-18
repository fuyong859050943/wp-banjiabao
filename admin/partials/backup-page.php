<div class="wrap bjbao-wrap">
    <h1>备份中心</h1>
    
    <div class="bjbao-backup-intro">
        <p>保护您的数据安全，定期备份是网站运营的基本保障。</p>
    </div>
    
    <!-- 创建备份面板 -->
    <div class="bjbao-panel">
        <h2>📦 创建新备份</h2>
        
        <form id="bjbao-backup-form">
            <div class="bjbao-backup-options">
                <div class="bjbao-option-group">
                    <label><strong>备份类型：</strong></label>
                    <label>
                        <input type="radio" name="backup_type" value="full" checked>
                        完整备份（文件 + 数据库）
                    </label>
                    <label>
                        <input type="radio" name="backup_type" value="files">
                        仅文件
                    </label>
                    <label>
                        <input type="radio" name="backup_type" value="database">
                        仅数据库
                    </label>
                </div>
                
                <div class="bjbao-option-group">
                    <label><strong>存储位置：</strong></label>
                    <label>
                        <input type="radio" name="storage" value="local" checked>
                        本地存储
                    </label>
                    <?php if ($has_cloud) : ?>
                        <?php if (!empty($cloud_config['aliyun'])) : ?>
                            <label>
                                <input type="radio" name="storage" value="aliyun">
                                阿里云 OSS
                            </label>
                        <?php endif; ?>
                        <?php if (!empty($cloud_config['tencent'])) : ?>
                            <label>
                                <input type="radio" name="storage" value="tencent">
                                腾讯云 COS
                            </label>
                        <?php endif; ?>
                    <?php else : ?>
                        <label class="bjbao-cloud-hint">
                            <input type="radio" name="storage" value="cloud" disabled>
                            云存储（需先 <a href="<?php echo admin_url('admin.php?page=wp-banjiabao-cloud'); ?>">配置云存储</a>）
                        </label>
                    <?php endif; ?>
                </div>
                
                <div class="bjbao-option-group">
                    <label for="backup_name"><strong>备份名称（可选）：</strong></label>
                    <input type="text" name="backup_name" id="backup_name" placeholder="留空则自动生成">
                </div>
            </div>
            
            <button type="submit" class="button button-primary button-hero bjbao-btn-backup">
                <span class="dashicons dashicons-upload" style="margin-top: 3px;"></span>
                开始备份
            </button>
        </form>
        
        <div class="bjbao-result" id="bjbao-backup-result" style="display: none;">
            <div class="bjbao-success">
                <span class="dashicons dashicons-yes-alt"></span>
                <div>
                    <strong>备份成功！</strong>
                    <p>备份大小：<span id="backup-size"></span></p>
                    <a href="#" id="bjbao-download-backup" class="button">下载备份</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 备份列表 -->
    <div class="bjbao-panel">
        <h2>📋 备份列表</h2>
        
        <?php if (empty($backups)) : ?>
            <div class="bjbao-empty">
                <span class="dashicons dashicons-archive" style="font-size: 50px; color: #ccc;"></span>
                <p>暂无备份记录</p>
            </div>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th width="30%">备份名称</th>
                        <th width="20%">大小</th>
                        <th width="25%">创建时间</th>
                        <th width="25%">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup) : ?>
                        <tr data-file="<?php echo esc_attr($backup['file']); ?>">
                            <td><strong><?php echo esc_html($backup['name']); ?></strong></td>
                            <td><?php echo esc_html($backup['size_formatted']); ?></td>
                            <td><?php echo esc_html($backup['date_formatted']); ?></td>
                            <td>
                                <a href="<?php echo esc_url($backup['file']); ?>" class="button button-small">下载</a>
                                <button type="button" class="button button-small button-primary bjbao-btn-restore" data-file="<?php echo esc_attr($backup['file']); ?>">恢复</button>
                                <button type="button" class="button button-small button-link-delete bjbao-btn-delete" data-file="<?php echo esc_attr($backup['file']); ?>">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- 恢复确认模态框 -->
<div id="bjbao-restore-modal" class="bjbao-modal" style="display: none;">
    <div class="bjbao-modal-content">
        <h2>确认恢复备份</h2>
        <p>确定要恢复这个备份吗？这将覆盖当前的所有数据！</p>
        
        <div class="bjbao-url-replace-inline">
            <label>
                <input type="checkbox" name="replace_url" id="modal-replace-url">
                替换域名
            </label>
            <div id="modal-url-fields" style="display: none;">
                <input type="text" name="old_url" id="modal-old-url" placeholder="旧站 URL">
                <input type="text" name="new_url" id="modal-new-url" placeholder="新站 URL" value="<?php echo get_site_url(); ?>">
            </div>
        </div>
        
        <div class="bjbao-modal-actions">
            <button type="button" class="button bjbao-modal-cancel">取消</button>
            <button type="button" class="button button-primary bjbao-modal-confirm">确认恢复</button>
        </div>
    </div>
</div>

<style>
.bjbao-wrap { max-width: 1000px; }
.bjbao-backup-intro {
    background: #f0f6fc;
    padding: 15px 20px;
    border-radius: 5px;
    border-left: 4px solid #27ae60;
    margin: 20px 0;
}
.bjbao-backup-intro p { margin: 0; }
.bjbao-panel {
    background: white;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 25px;
    margin: 20px 0;
}
.bjbao-panel h2 { margin-top: 0; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; }
.bjbao-backup-options { margin: 20px 0; }
.bjbao-option-group { margin: 15px 0; }
.bjbao-option-group label { display: flex; align-items: center; gap: 8px; margin: 8px 0; }
.bjbao-option-group input[type="text"] {
    width: 100%;
    max-width: 400px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
}
.bjbao-cloud-hint { color: #999; }
.bjbao-cloud-hint a { color: #2271b1; }
.bjbao-btn-backup { width: 100%; padding: 15px !important; font-size: 16px !important; }
.bjbao-result { margin-top: 20px; }
.bjbao-success {
    display: flex;
    gap: 15px;
    padding: 20px;
    background: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 8px;
    color: #155724;
}
.bjbao-success .dashicons { font-size: 40px; color: #28a745; }
.bjbao-success p { margin: 5px 0; }
.bjbao-empty {
    text-align: center;
    padding: 50px;
    color: #999;
}
.bjbao-empty p { margin: 10px 0 0 0; }
/* 模态框 */
.bjbao-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bjbao-modal-content {
    background: white;
    padding: 30px;
    border-radius: 10px;
    max-width: 500px;
    width: 90%;
}
.bjbao-modal-content h2 { margin-top: 0; }
.bjbao-modal-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
.bjbao-url-replace-inline { margin: 15px 0; }
.bjbao-url-replace-inline input[type="text"] {
    width: 100%;
    padding: 8px;
    margin: 5px 0;
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>

<script>
jQuery(document).ready(function($) {
    var restoreFile = '';
    
    // 创建备份
    $('#bjbao-backup-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('.bjbao-btn-backup');
        var originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<span class="spinner"></span> 备份中...');
        
        $.ajax({
            url: bjbao.ajax_url,
            type: 'POST',
            data: {
                action: 'banjiabao_backup',
                nonce: bjbao.nonce,
                backup_type: $form.find('[name="backup_type"]:checked').val(),
                storage: $form.find('[name="storage"]:checked').val(),
                backup_name: $form.find('[name="backup_name"]').val()
            },
            success: function(res) {
                if (res.success) {
                    $('#backup-size').text(res.data.size);
                    $('#bjbao-download-backup').attr('href', res.data.file);
                    $('#bjbao-backup-result').show();
                    location.reload();
                } else {
                    alert(bjbao.strings.error + res.data);
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                alert(bjbao.strings.error + '网络错误');
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // 打开恢复模态框
    $('.bjbao-btn-restore').on('click', function() {
        restoreFile = $(this).data('file');
        $('#bjbao-restore-modal').show();
    });
    
    // 模态框 URL 替换开关
    $('#modal-replace-url').on('change', function() {
        $('#modal-url-fields').toggle($(this).is(':checked'));
    });
    
    // 取消按钮
    $('.bjbao-modal-cancel').on('click', function() {
        $('#bjbao-restore-modal').hide();
    });
    
    // 确认恢复
    $('.bjbao-modal-confirm').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('恢复中...');
        
        $.ajax({
            url: bjbao.ajax_url,
            type: 'POST',
            data: {
                action: 'banjiabao_restore',
                nonce: bjbao.nonce,
                backup_file: restoreFile,
                replace_url: $('#modal-replace-url').is(':checked'),
                old_url: $('#modal-old-url').val(),
                new_url: $('#modal-new-url').val()
            },
            success: function(res) {
                if (res.success) {
                    alert('恢复成功！');
                    location.reload();
                } else {
                    alert(bjbao.strings.error + res.data);
                    $btn.prop('disabled', false).text('确认恢复');
                }
            },
            error: function() {
                alert(bjbao.strings.error + '网络错误');
                $btn.prop('disabled', false).text('确认恢复');
            }
        });
    });
    
    // 删除备份
    $('.bjbao-btn-delete').on('click', function() {
        if (!confirm(bjbao.strings.confirm_delete)) {
            return;
        }
        
        var $btn = $(this);
        var file = $btn.data('file');
        
        $.ajax({
            url: bjbao.ajax_url,
            type: 'POST',
            data: {
                action: 'banjiabao_delete_backup',
                nonce: bjbao.nonce,
                backup_file: file
            },
            success: function(res) {
                if (res.success) {
                    $btn.closest('tr').fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    alert(bjbao.strings.error + res.data);
                }
            }
        });
    });
});
</script>