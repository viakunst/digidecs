<?php

// Define basic constants
define('BASE_DIR', '/var/www/digidecs.svsticky.nl/');
define('TEMPLATES_DIR', BASE_DIR . 'templates/');

// Define email constants
define('EMAIL_TO_ADDRESS', 'addressee@something.org');
define('EMAIL_TO_NAME', 'R. ecipient');
define('EMAIL_SUBJECT_BASE', 'Receipt:');
define('EMAIL_FIRST_NAME', 'Roderick');

// Mailgun settings
define('EMAIL_API_KEY', '');
define('EMAIL_DOMAIN', 'something.org');

// Define upload restrictions
define('FILE_MAX_FILESIZE', 20971520); // 20 MiB
$allowed_filetypes  =
        array(
				'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'pdf' => 'application/pdf',
        );

?>
