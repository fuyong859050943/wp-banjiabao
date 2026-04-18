<div class="wrap bjbao-wrap">
    <h1>WP 搬家宝 - 一站式站点迁移与备份</h1>
    
    <div class="bjbao-header">
        <div class="bjbao-logo">
            <span class="dashicons dashicons-database-import" style="font-size: 48px; width: 48px; height: 48px;"></span>
        </div>
        <div class="bjbao-info">
            <h2>欢迎使用 WP 搬家宝</h2>
            <p>简单、高效、可靠的 WordPress 站点迁移与备份解决方案</p>
        </div>
    </div>
    
    <div class="bjbao-cards">
        <div class="bjbao-card bjbao-card-migration">
            <div class="bjbao-card-icon">
                <span class="dashicons dashicons-randomize"></span>
            </div>
            <h3>站点迁移</h3>
            <p>将您的网站完整迁移到新服务器或新域名，简单三步搞定</p>
            <a href="<?php echo admin_url('admin.php?page=wp-banjiabao-migration'); ?>" class="button button-primary">开始迁移</a>
        </div>
        
        <div class="bjbao-card bjbao-card-backup">
            <div class="bjbao-card-icon">
                <span class="dashicons dashicons-archive"></span>
            </div>
            <h3>备份中心</h3>
            <p>自动或手动备份您的网站数据，支持本地和云端存储</p>
            <a href="<?php echo admin_url('admin.php?page=wp-banjiabao-backup'); ?>" class="button button-primary">备份管理</a>
        </div>
        
        <div class="bjbao-card bjbao-card-cloud">
            <div class="bjbao-card-icon">
                <span class="dashicons dashicons-cloud"></span>
            </div>
            <h3>云端存储</h3>
            <p>连接阿里云 OSS 或腾讯云 COS，数据更安全</p>
            <a href="<?php echo admin_url('admin.php?page=wp-banjiabao-cloud'); ?>" class="button">配置云存储</a>
        </div>
    </div>
    
    <div class="bjbao-recent">
        <h2>📦 最近备份</h2>
        
        <?php if (empty($records)) : ?>
            <div class="bjbao-empty">
                <p>暂无备份记录。 <a href="<?php echo admin_url('admin.php?page=wp-banjiabao-backup'); ?>">立即创建第一个备份</a></p>
            </div>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th width="30%">备份名称</th>
                        <th width="15%">类型</th>
                        <th width="20%">大小</th>
                        <th width="25%">时间</th>
                        <th width="10%">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $records = array_reverse($records);
                    foreach (array_slice($records, 0, 5) as $record) : 
                    ?>
                        <tr>
                            <td><strong><?php echo esc_html($record['name']); ?></strong></td>
                            <td><?php echo esc_html($record['type']); ?></td>
                            <td><?php echo size_format($record['size']); ?></td>
                            <td><?php echo esc_html($record['time']); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=wp-banjiabao-backup'); ?>" class="button button-small">查看</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <div class="bjbao-footer">
        <p>WP 搬家宝 v<?php echo WP_BANJIABAO_VERSION; ?> | 
        <a href="<?php echo admin_url('admin.php?page=wp-banjiabao-settings'); ?>">设置</a> | 
        <a href="https://wordpress.org/support/plugin/wp-banjiabao" target="_blank">帮助</a></p>
    </div>
</div>

<style>
.bjbao-wrap { max-width: 1200px; }
.bjbao-header { 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white; 
    padding: 30px; 
    border-radius: 10px; 
    margin: 20px 0;
    display: flex;
    align-items: center;
    gap: 20px;
}
.bjbao-logo { flex-shrink: 0; }
.bjbao-logo .dashicons { color: white; }
.bjbao-info h2 { margin: 0 0 5px 0; color: white; }
.bjbao-info p { margin: 0; opacity: 0.9; }
.bjbao-cards { 
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
    gap: 20px; 
    margin: 30px 0;
}
.bjbao-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 25px;
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
}
.bjbao-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}
.bjbao-card-icon { margin-bottom: 15px; }
.bjbao-card-icon .dashicons {
    font-size: 50px;
    width: 50px;
    height: 50px;
    color: #666;
}
.bjbao-card-migration .bjbao-card-icon .dashicons { color: #e67e22; }
.bjbao-card-backup .bjbao-card-icon .dashicons { color: #27ae60; }
.bjbao-card-cloud .bjbao-card-icon .dashicons { color: #3498db; }
.bjbao-card h3 { margin: 0 0 10px 0; }
.bjbao-card p { color: #666; font-size: 14px; margin-bottom: 15px; }
.bjbao-recent { 
    background: white; 
    padding: 25px; 
    border-radius: 10px; 
    border: 1px solid #ddd;
    margin: 20px 0;
}
.bjbao-recent h2 { margin-top: 0; }
.bjbao-empty { 
    text-align: center; 
    padding: 40px; 
    color: #666;
    background: #f5f5f5;
    border-radius: 5px;
}
.bjbao-footer { 
    text-align: center; 
    padding: 20px; 
    color: #999; 
    font-size: 13px;
}
.bjbao-footer a { color: #666; }
</style>