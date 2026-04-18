/**
 * WP 搬家宝 - 后台脚本
 */

(function($) {
    'use strict';
    
    /**
     * 全局状态
     */
    var state = {
        isProcessing: false,
        currentFile: null,
    };
    
    /**
     * 初始化
     */
    function init() {
        setupAjax();
        setupForms();
        setupModals();
    }
    
    /**
     * 设置 AJAX
     */
    function setupAjax() {
        // 全局 AJAX 设置
        $.ajaxSetup({
            type: 'POST',
            dataType: 'json',
            timeout: 300000, // 5 分钟超时
        });
        
        // AJAX 错误处理
        $(document).ajaxError(function(event, xhr, settings, error) {
            console.error('AJAX Error:', error);
            
            if (xhr.status === 0) {
                showNotice('error', '网络错误，请检查网络连接');
            } else if (xhr.status === 403) {
                showNotice('error', '权限不足，请重新登录');
            } else if (xhr.status === 404) {
                showNotice('error', '请求的页面不存在');
            } else {
                showNotice('error', '请求失败: ' + error);
            }
        });
    }
    
    /**
     * 设置表单
     */
    function setupForms() {
        // 所有表单提交
        $('form[id^="bjbao-"]').on('submit', function(e) {
            e.preventDefault();
            
            if (state.isProcessing) {
                return;
            }
            
            var $form = $(this);
            var action = $form.attr('id').replace('bjbao-', '').replace('-form', '');
            
            processForm($form, action);
        });
    }
    
    /**
     * 处理表单
     */
    function processForm($form, action) {
        state.isProcessing = true;
        
        var $submitBtn = $form.find('[type="submit"], .button-primary');
        var originalText = $submitBtn.text();
        
        $submitBtn.prop('disabled', true);
        $submitBtn.html('<span class="spinner"></span> 处理中...');
        
        var data = {
            action: 'banjiabao_' + action,
            nonce: bjbao.nonce,
        };
        
        // 收集表单数据
        $form.serializeArray().forEach(function(item) {
            if (item.name === 'settings[auto_backup]') {
                data[item.name] = $form.find('[name="' + item.name + '"]').is(':checked') ? '1' : '0';
            } else {
                data[item.name] = item.value;
            }
        });
        
        // 文件上传处理
        if ($form.attr('enctype') === 'multipart/form-data') {
            var formData = new FormData($form[0]);
            formData.append('action', 'banjiabao_' + action);
            formData.append('nonce', bjbao.nonce);
            
            $.ajax({
                url: bjbao.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    handleResponse(response, action, $form, $submitBtn, originalText);
                },
                error: function(xhr) {
                    state.isProcessing = false;
                    $submitBtn.prop('disabled', false).text(originalText);
                    showNotice('error', '请求失败');
                }
            });
        } else {
            $.ajax({
                url: bjbao.ajax_url,
                data: data,
                success: function(response) {
                    handleResponse(response, action, $form, $submitBtn, originalText);
                },
                error: function(xhr) {
                    state.isProcessing = false;
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        }
    }
    
    /**
     * 处理响应
     */
    function handleResponse(response, action, $form, $submitBtn, originalText) {
        state.isProcessing = false;
        $submitBtn.prop('disabled', false).text(originalText);
        
        if (response.success) {
            showNotice('success', response.data.message || response.data || '操作成功！');
            
            // 如果有文件下载地址
            if (response.data.file) {
                showDownloadLink(response.data.file);
            }
            
            // 如果是刷新操作
            if (action === 'backup' || action === 'export') {
                setTimeout(function() {
                    location.reload();
                }, 1500);
            }
        } else {
            showNotice('error', response.data || '操作失败');
        }
    }
    
    /**
     * 设置模态框
     */
    function setupModals() {
        // 点击模态框外部关闭
        $(document).on('click', '.bjbao-modal', function(e) {
            if ($(e.target).hasClass('bjbao-modal')) {
                $(this).hide();
            }
        });
        
        // ESC 关闭
        $(document).on('keyup', function(e) {
            if (e.key === 'Escape') {
                $('.bjbao-modal:visible').hide();
            }
        });
    }
    
    /**
     * 显示通知
     */
    function showNotice(type, message) {
        // 移除旧通知
        $('.bjbao-notice-fixed').remove();
        
        var $notice = $('<div class="bjbao-notice-fixed bjbao-notice-' + type + ' bjbao-notice">')
            .html('<span class="dashicons dashicons-' + (type === 'success' ? 'yes-alt' : 'dismiss') + '"></span>')
            .append('<span>' + message + '</span>');
        
        $notice.appendTo('body').fadeIn();
        
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    /**
     * 显示下载链接
     */
    function showDownloadLink(fileUrl) {
        var $link = $('<a href="' + fileUrl + '" class="button button-primary" style="margin-left: 10px;">下载文件</a>');
        
        $('.bjbao-result:visible').find('.button').after($link);
    }
    
    /**
     * 格式化文件大小
     */
    function formatSize(bytes) {
        if (bytes === 0) return '0 B';
        
        var k = 1024;
        var sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    /**
     * 格式化日期
     */
    function formatDate(timestamp) {
        var date = new Date(timestamp * 1000);
        return date.toLocaleString('zh-CN');
    }
    
    // 文档加载完成后初始化
    $(document).ready(init);
    
    // 暴露给全局
    window.BanJiaBao = {
        showNotice: showNotice,
        formatSize: formatSize,
        formatDate: formatDate,
    };
    
})(jQuery);