<?php

// Define email constants
define('EMAIL_SUBJECT_BASE', 'Declaratie ingediend:');

// SMTP settings
define('EMAIL_SMTP_SERVER', 'mail.viakunst-utrecht.nl');
define('EMAIL_SMTP_PORT', 25);
define('EMAIL_SMTP_USERNAME', '');
define('EMAIL_SMTP_PASSWORD', '');

// Define upload restrictions
define('FILE_MAX_FILESIZE', 20971520); // 20 MiB
$allowed_filetypes  =
        array(
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'pdf' => 'application/pdf',
        );

$recipients =
        array(
                'aco' => array(
                        'label'     => 'Activiteitencommissie',
                        'mail'      => 'activiteitencommissie@viakunst-utrecht.nl',
                        'name'      => 'Activiteitencommisie',
                        'firstname' => 'aco'
                ),
                'other' => array(
                        'label'     => 'Overig (Bestuur)',
                        'mail'      => 'penningmeester@viakunst-utrecht.nl',
                        'name'      => 'Penningmeester',
                        'firstname' => 'penningmeester'
                )
        );

?>