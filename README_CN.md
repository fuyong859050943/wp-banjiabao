# WP 搬家宝

> 站点迁移 + 云端备份，一站式解决

## 📋 功能特性

### 🏠 站点迁移
- 简单三步完成网站迁移（导出 → 导入 → 完成）
- 支持跨服务器迁移
- 支持域名替换
- 极简界面，幼儿园小朋友都能看懂

### 💾 备份中心
- 支持完整备份、仅文件、仅数据库
- 本地备份无限制
- 云端备份（阿里云 OSS、腾讯云 COS）
- 定时自动备份

### ☁️ 云端存储
- 阿里云 OSS 原生支持
- 腾讯云 COS 原生支持
- 一键测试连接

## 📁 目录结构

```
wp-banjiabao/
├── wp-banjiabao.php          # 主入口文件
├── readme.txt                 # WordPress.org 描述文件
├── admin/
│   ├── class-admin.php       # 后台管理类
│   ├── css/
│   │   └── admin.css         # 后台样式
│   ├── js/
│   │   └── admin.js          # 后台脚本
│   └── partials/
│       ├── main-page.php     # 主页面模板
│       ├── migration-page.php # 迁移页面模板
│       ├── backup-page.php   # 备份页面模板
│       ├── cloud-page.php    # 云存储页面模板
│       └── settings-page.php # 设置页面模板
├── includes/
│   ├── class-banjiabao-loader.php     # 加载器
│   ├── class-banjiabao-database.php   # 数据库处理
│   ├── class-banjiabao-files.php      # 文件处理
│   ├── class-banjiabao-cloud.php      # 云存储
│   ├── class-banjiabao-migration.php  # 迁移功能
│   └── class-banjiabao-backup.php     # 备份功能
└── vendor/
    └── composer.json         # Composer 依赖
```

## 🚀 安装

### 方式一：WordPress 后台上传安装
1. 下载插件压缩包
2. WordPress 后台 → 插件 → 安装插件 → 上传
3. 启用插件

### 方式二：手动安装
1. 解压插件到 `/wp-content/plugins/wp-banjiabao/`
2. 在 WordPress 后台启用插件

## ⚙️ 配置

### 云存储配置

#### 阿里云 OSS
1. 进入「云存储」设置页面
2. 填写 AccessKey ID、AccessKey Secret、EndPoint、Bucket 名称
3. 点击「测试连接」
4. 保存配置

#### 腾讯云 COS
1. 进入「云存储」设置页面
2. 切换到「腾讯云 COS」标签
3. 填写 SecretId、SecretKey、地域、Bucket 名称
4. 点击「测试连接」
5. 保存配置

## 🔧 开发

### 安装依赖

```bash
cd wp-banjiabao
composer install
```

### WordPress 环境要求

- WordPress 5.0 或更高
- PHP 7.4 或更高
- PHP ZipArchive 扩展

## 📝 版本历史

### 1.0.0
- 首次发布
- 支持站点迁移
- 支持本地备份
- 支持阿里云 OSS
- 支持腾讯云 COS
- 支持定时备份

## 📄 许可证

GPL v2 or later

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

## 📧 支持

- **GitHub Issues**: [https://github.com/fuyong859050943/wp-banjiabao/issues](https://github.com/fuyong859050943/wp-banjiabao/issues)
- **Twitter**: [@wp_banjiabao](https://twitter.com/wp_banjiabao)

---

**English Documentation**: [README.md](README.md)