# Source Protection Guide: Obfuscation & Encryption

This guide explains how to protect your Laravel source code using a two-stage process: **Obfuscation** (making code unreadable) and **Encryption** (hiding code logic).

---

## Stage 1: Obfuscation (Using YakPro-Po)

YakPro-Po (Yet Another Killer PHP Obfuscator) scrambles variable names, function names, and logic to make reverse-engineering extremely difficult.

### 1. Setup YakPro-Po
1. Clone or download YakPro-Po from its repository.
2. Ensure you have `nikic/php-parser` installed (usually in the YakPro-Po folder).
3. Copy the [yakpro-po.php](file:///d:/DMS_HNB/yakpro-po.php) file to your project root or a central tools folder.

### 2. Configuration ([yakpro-po.cnf](file:///d:/DMS_HNB/dms_backend_hnb/YakPro-Po/yakpro-po.cnf))
Create a [yakpro-po.cnf](file:///d:/DMS_HNB/dms_backend_hnb/YakPro-Po/yakpro-po.cnf) file with these recommended settings:

```php
<?php
$conf->t_ignore_pre_defined_classes = 'all';
$conf->parser_mode = 'PREFER_PHP7'; // Use PREFER_PHP7 for higher performance
$conf->scramble_mode = 'identifier';
$conf->scramble_length = 8;
$conf->obfuscate_variable_name = true;
$conf->obfuscate_function_name = true;
$conf->obfuscate_class_name = true;
$conf->obfuscate_property_name = true;
$conf->obfuscate_method_name = true;
$conf->shuffle_stmts = true; // Recommended for maximum protection
$conf->strip_indentation = true;
```

### 3. Execution
Run YakPro-Po against your [app](file:///d:/DMS_HNB/dms_backend_hnb/app/Http/Controllers/LicenseController.php#22-42) directory:
```bash
php yakpro-po.php app -o app_obfuscated
```
Then replace your [app](file:///d:/DMS_HNB/dms_backend_hnb/app/Http/Controllers/LicenseController.php#22-42) folder with the contents of `app_obfuscated`.

---

## Stage 2: Encryption (Using Laravel Source Encrypter)

Once obfuscated, we further encrypt the files so they cannot be read as plain text at all.

### 1. Install via Composer
Run the following command in your project:
```bash
composer require sbamtr/laravel-source-encrypter --dev
```

### 2. Configure ([config/source-encrypter.php](file:///d:/DMS_HNB/dms_backend_hnb/config/source-encrypter.php))
Publish the config:
```bash
php artisan vendor:publish --provider="Sbamtr\LaravelSourceEncrypter\SourceEncrypterServiceProvider"
```

Set your source paths:
```php
return [
    'source'      => ['app', 'database', 'routes'], // Path(s) to encrypt
    'destination' => 'encrypted', // Destination path
    'key_length'  => 6, // Encryption key length
];
```

### 3. Execution
Run the encryption command:
```bash
php artisan encrypt-source
```
This will create an `encrypted` folder containing the protected version of your code.

---

## Best Practices for Deployment

1.  **Keep Backups**: Always keep an un-obfuscated, un-encrypted version of your source code in Git or a secure backup.
2.  **Order Matters**: Always **Obfuscate FIRST**, then **Encrypt**.
3.  **Test Thoroughly**: Protected code can sometimes have issues with reflection or dynamic string-based class names. Test your app fully after protection.
4.  **Vendor stays clear**: Do NOT encrypt the `vendor` folder; it will break dependencies.
