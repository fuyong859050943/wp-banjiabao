<div class="wrap bjbao-wrap">
    <h1>站点迁移中心</h1>
    
    <div class="bjbao-migration-intro">
        <p>简单三步，轻松完成网站迁移：<strong>导出 → 导入 → 完成！</strong></p>
    </div>
    
    <div class="bjbao-two-columns">
        <!-- 导出面板 -->
        <div class="bjbao-panel">
            <h2>📤 导出站点</h2>
            
            <div class="bjbao-preview" id="bjbao-export-preview">
                <div class="bjbao-preview-item">
                    <span class="label">文件数量：</span>
                    <span class="value" id="preview-files-count">加载中...</span>
                </div>
                <div class="bjbao-preview-item">
                    <span class="label">文件大小：</span>
                    <span class="value" id="preview-files-size">加载中...</span>
                </div>
                <div class="bjbao-preview-item">
                    <span class="label">数据库：</span>
                    <span class="value" id="preview-db-size">加载中...</span>
                </div>
                <div class="bjbao-preview-item bjbao-preview-total">
                    <span class="label">预计总大小：</span>
                    <span class="value" id="preview-total-size">加载中...</span>
                </div>
            </div>
            
            <form id="bjbao-export-form">
                <label>
                    <input type="checkbox" name="include_files" checked>
                    包含网站文件（主题、插件、图片等）
                </label>
                
                <label>
                    <input type="checkbox" name="include_database" checked>
                    包含数据库（文章、评论、设置等）
                </label>
                
                <button type="submit" class="button button-primary button-hero bjbao-btn-export">
                    <span class="dashicons dashicons-download" style="margin-top: 3px;"></span>
                    开始导出
                </button>
            </form>
            
            <div class="bjbao-result" id="bjbao-export-result" style="display: none;">
                <div class="bjbao-success">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <div>
                        <strong>导出成功！</strong>
                        <p>备份大小：<span id="export-size"></span></p>
                        <a href="#" id="bjbao-download-export" class="button">下载备份文件</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 导入面板 -->
        <div class="bjbao-panel">
            <h2>📥 导入站点</h2>
            
            <form id="bjbao-import-form" enctype="multipart/form-data">
                <div class="bjbao-file-upload">
                    <input type="file" name="backup_file" id="bjbao-import-file" accept=".zip" required>
                    <label for="bjbao-import-file">
                        <span class="dashicons dashicons-upload"></span>
                        点击选择备份文件 或 拖拽文件到此处
                    </label>
                    <div class="bjbao-file-name" id="bjbao-file-name"></div>
                </div>
                
                <div class="bjbao-url-replace">
                    <label>
                        <input type="checkbox" name="replace_url" id="bjbao-replace-url">
                        替换域名（从旧站迁移到新站时勾选）
                    </label>
                    
                    <div class="bjbao-url-fields" id="bjbao-url-fields" style="display: none;">
                        <input type="text" name="old_url" id="bjbao-old-url" placeholder="旧站 URL，例如：https://old-site.com">
                        <span class="dashicons dashicons-arrow-right-alt" style="margin: 5px 0;"></span>
                        <input type="text" name="new_url" id="bjbao-new-url" placeholder="新站 URL，例如：https://new-site.com" value="<?php echo get_site_url(); ?>">
                    </div>
                </div>
                
                <div class="bjbao-warning">
                    <span class="dashicons dashicons-warning"></span>
                    <p><strong>警告：</strong>导入将覆盖当前站点的数据和文件！请确保已备份当前站点，或在测试站点上进行操作。</p>
                </div>
                
                <button type="submit" class="button button-primary button-hero bjbao-btn-import">
                    <span class="dashicons dashicons-upload" style="margin-top: 3px;"></span>
                    开始导入
                </button>
            </form>
            
            <div class="bjbao-result" id="bjbao-import-result" style="display: none;">
                <div class="bjbao-success">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <div>
                        <strong>导入成功！</strong>
                        <p>您的网站已成功恢复到备份版本。</p>
                        <a href="<?php echo get_site_url(); ?>" class="button" target="_blank">访问网站</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bjbao-wrap { max-width: 1200px; }
.bjbao-migration-intro {
    background: #f0f6fc;
    padding: 15px 20px;
    border-radius: 5px;
    border-left: 4px solid #2271b1;
    margin: 20px 0;
}
.bjbao-migration-intro p { margin: 0; font-size: 16px; }
.bjbao-two-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    margin: 20px 0;
}
@media (max-width: 900px) {
    .bjbao-two-columns { grid-template-columns: 1fr; }
}
.bjbao-panel {
    background: white;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 25px;
}
.bjbao-panel h2 { margin-top: 0; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; }
.bjbao-preview {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
}
.bjbao-preview-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}
.bjbao-preview-item:last-child { border-bottom: none; }
.bjbao-preview-item .label { color: #666; }
.bjbao-preview-item .value { font-weight: bold; }
.bjbao-preview-total { 
    margin-top: 10px; 
    padding-top: 10px; 
    border-top: 2px solid #ddd;
    font-size: 1.1em;
}
.bjbao-preview-total .value { color: #e67e22; }
#bjbao-export-form label {
    display: block;
    padding: 10px 0;
    cursor: pointer;
}
#bjbao-export-form label input { margin-right: 10px; }
.bjbao-btn-export, .bjbao-btn-import {
    width: 100%;
    margin-top: 20px;
    padding: 15px !important;
    font-size: 16px !important;
}
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
.bjbao-file-upload {
    position: relative;
    margin: 15px 0;
}
.bjbao-file-upload input[type="file"] {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}
.bjbao-file-upload label {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 40px 20px;
    border: 2px dashed #ccc;
    border-radius: 8px;
    color: #666;
    cursor: pointer;
}
.bjbao-file-upload label .dashicons { font-size: 50px; margin-bottom: 10px; }
.bjbao-file-upload:hover label {
    border-color: #2271b1;
    background: #f0f6fc;
}
.bjbao-file-name {
    margin-top: 10px;
    padding: 10px;
    background: #e8f0fe;
    border-radius: 5px;
    text-align: center;
    font-weight: bold;
}
.bjbao-url-replace { margin: 20px 0; }
.bjbao-url-replace label { display: flex; align-items: center; gap: 8px; }
.bjbao-url-fields { margin-top: 15px; }
.bjbao-url-fields input {
    width: 100%;
    padding: 10px;
    margin: 5px 0;
    border: 1px solid #ddd;
    border-radius: 5px;
}
.bjbao-warning {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 5px;
    padding: 15px;
    margin: 15px 0;
}
.bjbao-warning .dashicons { color: #856404; float: left; margin-right: 10px; }
.bjbao-warning p { margin: 0; color: #856404; font-size: 14px; }
</style>

<script>
jQuery(document).ready(function($) {
    // 加载预览信息
    function loadPreview() {
        $.ajax({
            url: bjbao.ajax_url,
            type: 'POST',
            data: {
                action: 'banjiabao_get_preview',
                nonce: bjbao.nonce
            },
            success: function(res) {
                if (res.success) {
                    $('#preview-files-count').text(res.data.files_count + ' 个文件');
                    $('#preview-files-size').text(res.data.files_size_formatted);
                    $('#preview-db-size').text(res.data.database_size_formatted);
                    $('#preview-total-size').text(res.data.total_size_formatted);
                }
            }
        });
    }
    loadPreview();
    
    // 导出表单提交
    $('#bjbao-export-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('.bjbao-btn-export');
        var originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<span class="spinner"></span> 导出中...');
        
        $.ajax({
            url: bjbao.ajax_url,
            type: 'POST',
            data: {
                action: 'banjiabao_export',
                nonce: bjbao.nonce,
                include_files: $form.find('[name="include_files"]').is(':checked'),
                include_database: $form.find('[name="include_database"]').is(':checked')
            },
            success: function(res) {
                if (res.success) {
                    $('#export-size').text(res.data.size);
                    $('#bjbao-download-export').attr('href', res.data.file);
                    $('#bjbao-export-result').show();
                    $form.hide();
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
    
    // 文件选择显示文件名
    $('#bjbao-import-file').on('change', function() {
        var fileName = $(this)[0].files[0] ? $(this)[0].files[0].name : '';
        $('#bjbao-file-name').text(fileName);
    });
    
    // URL 替换开关
    $('#bjbao-replace-url').on('change', function() {
        $('#bjbao-url-fields').toggle($(this).is(':checked'));
    });
    
    // 导入表单提交
    $('#bjbao-import-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!confirm('确定要导入吗？这将覆盖当前站点的所有数据！')) {
            return;
        }
        
        var $form = $(this);
        var $btn = $form.find('.bjbao-btn-import');
        var originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<span class="spinner"></span> 导入中...');
        
        var formData = new FormData($form[0]);
        formData.append('action', 'banjiabao_import');
        formData.append('nonce', bjbao.nonce);
        
        $.ajax({
            url: bjbao.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $('#bjbao-import-result').show();
                    $form.hide();
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
});
</script>