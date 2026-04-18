=== WP BanJiaBao - WordPress Site Migration & Backup ===
Contributors: xiaxiami
Tags: migration, backup, restore, aliyun, tencent, cloud, oss, cos, wordpress, site-migration
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

One-click WordPress site migration and backup plugin with cloud storage support. Supports Alibaba Cloud OSS and Tencent Cloud COS.

== Description ==

WP BanJiaBao is a simple and powerful WordPress site migration and backup plugin.

**Key Features:**

* **Site Migration**: Simple 3-step process to migrate your website effortlessly
* **One-click Backup**: Full backup, files only, or database only options
* **Cloud Storage**: Supports Alibaba Cloud OSS and Tencent Cloud COS
* **Scheduled Backups**: Automatic daily/weekly backup schedules
* **Clean Interface**: User-friendly design that anyone can understand

**Why choose WP BanJiaBao?**

* Simple interface, easy to use
* Supports Chinese cloud storage providers
* Free version with useful features
* Chinese localization

= Free Version Features =

* Site migration up to 500MB
* Unlimited local backup
* Full backup / Files only / Database only
* Manual backup anytime

= Pro Version Features (Coming Soon) =

* Unlimited site migration size
* Cloud backup to Alibaba Cloud OSS / Tencent Cloud COS
* Scheduled automatic backups
* Incremental backup support
* Priority support

== Installation ==

1. Upload the `wp-banjiabao` folder to `/wp-content/plugins/`
2. Activate WP BanJiaBao from the WordPress Plugins page
3. Click "WP BanJiaBao" in the left menu to start using

**Cloud Storage Configuration (Optional):**

1. Go to "Cloud Storage" settings page
2. Enter your Alibaba Cloud OSS or Tencent Cloud COS configuration
3. Click "Test Connection" to verify settings
4. Save configuration

== Frequently Asked Questions ==

= Is this plugin secure? =

Yes. WP BanJiaBao follows WordPress security best practices. All operations require administrator permissions.

= What are the limitations of the free version? =

The free version supports site migration up to 500MB. Local backup features have no limitations.

= Which cloud providers are supported? =

Currently supports Alibaba Cloud OSS and Tencent Cloud COS.

= How to upgrade to Pro version? =

Coming soon! Stay tuned for updates.

= Does it work with multisite? =

Currently tested with single WordPress installations. Multisite support is planned for future releases.

= What if migration fails? =

Check that:
1. Your server has enough disk space
2. PHP ZipArchive extension is installed
3. PHP exec() function is enabled
4. File permissions are correct

== Screenshots ==

1. WP BanJiaBao main interface - Clean and intuitive dashboard
2. Site migration page - Simple 3-step migration process
3. Backup center - Flexible backup options
4. Cloud storage settings - Easy cloud configuration

== Changelog ==

= 1.0.0 =
* Initial release
* Site migration feature
* Local backup feature
* Alibaba Cloud OSS support
* Tencent Cloud COS support
* Scheduled backup support

== Upgrade Notice ==

= 1.0.0 =
First release! Try the free version with site migration and local backup features.