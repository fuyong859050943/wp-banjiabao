# WP BanJiaBao

> One-click WordPress site migration & backup plugin with cloud storage support

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## ✨ Features

### 🏠 Site Migration
- **One-click export/import** - Simple 3-step migration process
- **Cross-server migration** - Move your site anywhere
- **Domain replacement** - Automatic URL updates in database
- **User-friendly UI** - Clean interface that anyone can understand

### 💾 Backup Center
- **Full backup** - Files + database in one package
- **Selective backup** - Files only or database only
- **Local backup** - Unlimited local storage
- **Cloud backup** - Alibaba Cloud OSS, Tencent Cloud COS
- **Scheduled backups** - Automatic daily/weekly backups

### ☁️ Cloud Storage Support
- **Alibaba Cloud OSS** - Native integration
- **Tencent Cloud COS** - Native integration
- **One-click connection test** - Verify settings before saving

## 📦 Installation

### Method 1: WordPress Admin Upload
1. Download the plugin zip file
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload and activate

### Method 2: Manual Installation
1. Extract the plugin to `/wp-content/plugins/wp-banjiabao/`
2. Activate in WordPress Admin → Plugins

## ⚙️ Configuration

### Cloud Storage Setup

#### Alibaba Cloud OSS
1. Navigate to **Cloud Storage** settings
2. Enter your AccessKey ID, AccessKey Secret, Endpoint, and Bucket name
3. Click **Test Connection** to verify
4. Save settings

#### Tencent Cloud COS
1. Navigate to **Cloud Storage** settings
2. Switch to **Tencent Cloud COS** tab
3. Enter your SecretId, SecretKey, Region, and Bucket name
4. Click **Test Connection** to verify
5. Save settings

## 🔧 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- PHP ZipArchive extension
- PHP exec() function (for zip operations)

## 📁 Directory Structure

```
wp-banjiabao/
├── wp-banjiabao.php              # Main plugin file
├── admin/
│   ├── class-admin.php           # Admin controller
│   ├── css/admin.css             # Admin styles
│   ├── js/admin.js               # Admin scripts
│   └── partials/                 # Page templates
├── includes/
│   ├── class-banjiabao-loader.php    # Plugin loader
│   ├── class-banjiabao-database.php  # Database handler
│   ├── class-banjiabao-files.php     # File handler
│   ├── class-banjiabao-cloud.php     # Cloud storage
│   ├── class-banjiabao-migration.php # Migration logic
│   └── class-banjiabao-backup.php    # Backup logic
└── vendor/                       # Composer dependencies
```

## 🚀 Development

```bash
# Clone the repository
git clone https://github.com/fuyong859050943/wp-banjiabao.git

# Install dependencies
cd wp-banjiabao
composer install
```

## 📝 Changelog

### 1.0.0 (2026-04-18)
- Initial release
- Site migration feature
- Local backup feature
- Alibaba Cloud OSS support
- Tencent Cloud COS support
- Scheduled backup support

## 📄 License

GPL v2 or later

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📧 Support

- **GitHub Issues**: [https://github.com/fuyong859050943/wp-banjiabao/issues](https://github.com/fuyong859050943/wp-banjiabao/issues)
- **Twitter**: [@wp_banjiabao](https://twitter.com/wp_banjiabao)

---

**中文文档**: [README_CN.md](README_CN.md)