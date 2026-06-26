# License Generation Guide (Vendor Side)

This guide explains how to generate valid license keys for customers. This process should be performed **ONLY** by the vendor on a secure machine.

## 1. Security Warning 🚨
- **NEVER** share the [license_private.pem](file:///d:/DMS_HNB/license_generator/license_private.pem) file with anyone.
- **NEVER** include the private key or the generator script in the customer's codebase.
- Keep the private key in a secure, encrypted location.

## 2. Generating a Private/Public Key Pair
If you need to generate a new key pair, run these OpenSSL commands:

```bash
# Generate private key
openssl genrsa -out license_private.pem 2048

# Extract public key (This is what you ship to the customer)
openssl rsa -in license_private.pem -pubout -out license_public.pem
```

## 3. License Generation Script ([license-generator.php](file:///d:/DMS_HNB/license_generator/license-generator.php))

Create a script on your secure machine with the following content:

```php
<?php
/**
 * Vendor-side License Generator
 */

$PRIVATE_KEY_PATH = __DIR__ . '/license_private.pem';

// 1. Define the Payload
$payload = [
    'customer_id' => 'CUST-002', // Customer Unique ID
    'fingerprint' => 'sha256:...', // Get this from customer (php artisan license:fingerprint)
    'max_concurrent_users' => 10, // 0 for Unlimited users
    'start_date'  => (new DateTime())->format('Y-m-d H:i:s'),
    'expiry_date' => (new DateTime('+1 year'))->format('Y-m-d H:i:s'),
    'issued_at'   => (new DateTime())->format('Y-m-d H:i:s'),
    'license_id'  => bin2hex(random_bytes(16)),
    'version'     => '1.0'
];

$payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);

// 2. Sign the Payload
$privateKey = openssl_pkey_get_private(file_get_contents($PRIVATE_KEY_PATH));
if (!$privateKey) {
    die("❌ Invalid private key\n");
}

openssl_sign($payloadJson, $signature, $privateKey, OPENSSL_ALGO_SHA256);

// 3. Build the License Key String
$licenseKey = 'LIC-v1-' . 
              base64_encode($payloadJson) . '.' . 
              base64_encode($signature);

echo "✅ License Key Generated Successfully:\n\n$licenseKey\n";
```

## 4. How to Issue a License
1. Ask the customer to run `php artisan license:fingerprint` on their server.
2. Update the `$payload['fingerprint']` in the generator script with the value they provide.
3. Update the `customer_id` and `expiry_date` as needed.
4. Run the script: `php license-generator.php`.
5. Copy the generated `LIC-v1-...` string and send it to the customer.
6. The customer can then apply it using `php artisan license:apply {key}` or via their admin panel.
