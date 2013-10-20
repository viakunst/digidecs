<?php
// Load libraries and configuration
require_once('./config.php');
require_once(LIB_DIR . 'class.phpmailer.php');

// Load templates
$header      = file_get_contents(TEMPLATES_DIR . 'header.html'      );
$footer      = file_get_contents(TEMPLATES_DIR . 'footer.html'      );
$confirm     = file_get_contents(TEMPLATES_DIR . 'confirm.html'     );
$form        = file_get_contents(TEMPLATES_DIR . 'form.html'        );
$form_header = file_get_contents(TEMPLATES_DIR . 'form_header.html' );

// Check if all templates got loaded succesfully.
// If not throw an error.
if ( $header && $footer && $form && $confirm &&$form_header ) {
	// Show form if there isn't any POST data, this
	// is the first time a user loads the web-page
	if ( empty($_POST) ) {
		echo $header;
		echo $form_header;
		echo $form;
		echo $footer;
	}
	// There is POST data, a submission has been made.
	// The POST data and file need to be verified and
	// an email has to be sent to the treasurer.

	// If the data is incorect, a list of errors needs
	// to be shown.
	else {
		$validation_status = validateAll($_POST, $_FILES['ticket']);
		// Check if the amount of keys that hold the value
		// true is equal to the total amount of keys.
		// If yes; the submit is succesul.
		if ( count( array_keys($validation_status, true) )
					== count( $validation_status ) ) {
			handleSubmit($_POST, $_FILES['ticket']);
			echo $header;
			echo $confirm;
		}
		else {
      // TODO: show previous entries in form after
      // failure to pass validation.
			$errors = array_keys($validation_status, false);

			// Build the page like normal, but insert the
			// relevant error messages
			echo $header;
			echo $form_header;
			foreach ($errors as $error) {
				switch($error) {
					case "name":
						echo alertMessage("Vul je naam in.");
						break;
					case "email":
						echo alertMessage("Email-address niet geldig.");
						break;
					case "city":
						echo alertMessage("Vul je woonplaats in.");
						break;
					case "date":
						echo alertMessage("Datum moet in het formaat DD-MM-YYYY zijn");
						break;
					case "total-amount":
						echo alertMessage("Het bedrag moet een geldig cijfer zijn.");
						break;
					case "description":
						echo alertMessage("Vermeld wat je gekocht hebt");
						break;
					case "purpose":
						echo alertMessage("Vermeld waarvoor je het gekocht hebt");
						break;
					case "bank-account":
						echo alertMessage("Vul je rekeningnummer in");
						break;
					case "ticket":
						echo alertMessage("Het bonnetje kan maximaal 2MB groot zijn. Alleen .pdf, .jpg, .gif en .png bestanden mogen worden geupload");
						break;
					case "accept-tos":
						echo alertMessage("Je moet alles eerst checken voordat je een declaratie kan doen.");
						break;
					default:
						echo alertMessage("Je hebt een fout gemaakt, probeer het opnieuw.");
				}
			}
			echo $form;
			echo $footer;
		}
	}
}
// Templates did not load correctly. Throw
// error.
else {
	echo "505 internal server error.";
	header("HTTP/1.1 500 Internal Server Error");
	exit;
}
// Validation functions. Used when a POST has
// been made to see if all the data is okay.

// This function returns a key-value array
// with bools that are used in creating the
// error messages.
function validateAll($post, $file) {
	// Array that holds the validation status
	// of each of the submitted values.
	$validation_status = array(
		"name"         => false,
		"email"        => false,
		"city"         => false,
		"date"         => false,
		"total-amount" => false,
		"description"  => false,
		"purpose"		   => false,
		"bank-account" => false,
		"remarks"      => true,
		"ticket"       => false,
		"accept-tos"   => false
	);

	// Assign correct truth value to all keys
	// in the validationstatus array. Remarks
	// are always in the right format.

	// TODO: find a metaprogramming way around
	// listing all this stuff manually. Preferably
	// involving standard function naming.
	$validation_status['email']        = validateEmail($post['email']    );
	$validation_status['date']         = validateDate ($post['date']     );
	$validation_status['total-amount'] = is_numeric($post['total-amount']);
	$validation_status['name']         = !empty($post['name']            );
	$validation_status['city']         = !empty($post['city']            );
	$validation_status['description']  = !empty($post['description']     );
	$validation_status['purpose']      = !empty($post['purpose']         );
	$validation_status['bank-account'] = !empty($post['bank-account']    );
	$validation_status['ticket']       = validateTicket($file);
	$validation_status['accept-tos']   = $post['accept-tos'];

	return $validation_status;
}

// Validate email using the built in PHP
// filter. Does not support TLD addresses.
function validateEmail($email) {
	return filter_var($email, FILTER_VALIDATE_EMAIL)
			&& preg_match('/@.+\./', $email);
}
// Validates wheter a date is valid.
// The default format that checks happen on is
// DD-MM-YYY, but this can be changed by
// passing a DateTime format string.
function validateDate($date, $format = 'd-m-Y') {
	$d = DateTime::createFromFormat($format, $date);
	// A date is valid if it is a correct object
	// and the formatted version is equal to the
	// original string.
	return $d && $d->format($format) == $date;
}
// Validates wheter the uploaded file is of
// the correct type, size and has the correct
// extension.
function validateTicket($file) {
	// The ticket failed validation if there are
	// any errors with it.
	if ($file['error']>0) {
		return false;
	}
	// The original file extension is the last item
	// of the array of the original filename split
	// by periods.
	$file_ext = end( explode('.', $file['name']));
	global $allowed_extensions, $allowed_filetypes;

	// A file is valid if it has an allowed extension,
	// an allowed filetype and is less than or equal
	// in size to the maximum filesize.
	if(  in_array($file_ext, $allowed_extensions)
		&& in_array($file['type'], $allowed_filetypes)
		&& $file['size'] <= FILE_MAX_FILESIZE ) {
		return true;
	}
	return false;
}

// Generates a DOM string conaining a warning
// message.
function alertMessage($string) {
	return '<div class="alert alert-warning">' . $string . '</div>';
}

// Logic to handle a successfull submit.
// An email get's send to the treasurer
// with the ticket attached.
function handleSubmit($post, $file) {
	// Create new PHP mailer instance and
  // configure it
  $mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->IsHTML(false);
	$mail->AddAddress(EMAIL_TO_ADDRESS, EMAIL_TO_NAME);

	$mail->Host     = EMAIL_SMTP_SERVER;
	$mail->From     = $post['email'];
	$mail->FromName = $post['name'];
	$mail->Subject  = EMAIL_SUBJECT_BASE;
	$mail->Body     =
		"Hoi Marit,\n\n" .

		"Ik heb zojuist het DigiDecs formulier ingevuld. " .
		"Dit zijn mijn gegevens.\n\n" .

		"Naam: {$post['name']}\n" .
		"Email: {$post['email']}\n" .
		"Woonplaats: {$post['city']}\n" .
		"Datum aankoop: {$post['date']}\n" .
		"Totaalbedrag: {$post['total-amount']}\n" .
		"Wat: {$post['description']}\n" .
		"Waarvoor: {$post['purpose']}\n" .
		"Rekeningnummer: {$post['bank-account']}\n" .
		"Opmerkingen: {$post['remarks']}\n\n" .

		"Zou je me alsjeblieft terug kunnen betalen? :)\n\n" .

		"Groetjes,\n\n" .

		"{$post['name']}";

	// We need the file extension of the
	// original file to attach it properly
	$file_ext = end( explode('.', $file['name']));
	$mail->AddAttachment($file['tmp_name'], 'bon.' . $file_ext);

	if(!$mail->Send()) {
		echo "Email was not sent, try again.";
	}
}
