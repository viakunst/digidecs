<?php
// Define basic constants
define('BASE_DIR',      '');
define('TEMPLATES_DIR', BASE_DIR . 'templates/');
define('LIB_DIR',       BASE_DIR . 'lib/'      );

// Define email constants
define('EMAIL_TO_ADDRESS',   '');
define('EMAIL_TO_NAME',      '');
define('EMAIL_SMTP_SERVER',  '');
define('EMAIL_SUBJECT_BASE', '');

// Define upload restrictions
define('FILE_MAX_FILESIZE', 2000000);
$allowed_filetypes  =
	array('image/jpeg',
				'image/jpg',
				'image/png',
				'application/pdf',
				'application/x-pdf'	 );
$allowed_extensions =
	array('gif',
				'GIF',
				'jpeg',
				'JPEG',
				'jpg',
				'JPG',
				'png',
				'PNG',
				'pdf',
				'PDF'  );
