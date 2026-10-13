<?php
// Example only. Copy this file OUTSIDE the site's public directory to ../sat-test-drive-config.php.
// Never commit real passwords.
return [
 'db_host' => 'localhost',
 'db_name' => 'replace_database_name',
 'db_user' => 'replace_database_user',
 'db_password' => 'replace_database_password',
 // Generate locally with: php -r "echo password_hash('YOUR_STRONG_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
 'admin_password_hash' => 'paste_generated_hash_here',
 // Optional email notifications via hosting PHP mail().
 'notification_email' => 'onefreaklythug@gmail.com',
 // Sender must be accepted by your hosting mail setup.
 'mail_from' => 'no-reply@pricepvtest.online',
];
