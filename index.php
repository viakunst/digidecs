<?php

// Load configuration
require 'config.php';

// Load libraries
require 'vendor/autoload.php';
use Mailgun\Mailgun;

// Load templates
$header      = file_get_contents(TEMPLATES_DIR . 'header.html'      );
$footer      = file_get_contents(TEMPLATES_DIR . 'footer.html'      );
$confirm     = file_get_contents(TEMPLATES_DIR . 'confirm.html'     );
$form_header = file_get_contents(TEMPLATES_DIR . 'form_header.html' );
$form        = 					 TEMPLATES_DIR . 'form.php'          ;

// Check if all templates got loaded succesfully.
// If not throw an error.
if ( $header && $footer && $form && $confirm &&$form_header ) {
	// Show form if there isn't any POST data, this
	// is the first time a user loads the web-page
	if ( empty($_POST) ) {
		echo $header;
		echo $form_header;
		require $form;
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
			// TODO: Show the relevant errors according to the
			// validation functions.

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
					case "totalamount":
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
						// echo alertMessage("Het bonnetje kan maximaal 2MB groot zijn. Alleen .pdf, .jpg, .gif en .png bestanden mogen worden geupload");
						echo alertMessage("Het bonnetje kan maximaal 10MB groot zijn. Alleen .pdf, .png, .jpg en .gif bestanden mogen worden geupload");
						break;
					case "accept-tos":
						echo alertMessage("Je moet alles eerst checken voordat je een declaratie kan doen.");
						break;
					default:
						echo alertMessage("Je hebt een fout gemaakt, probeer het opnieuw.");
				}
			}
			require $form;
			echo $footer;
		}
	}
} else {
	echo "500 Internal Server Error";
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
		"totalamount"  => false,
		"description"  => false,
		"purpose"      => false,
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
	$validation_status['email']        = validateEmail ($post['email']    );
	$validation_status['totalamount']  = validateAmount($post['totalamount']);
	$validation_status['name']         = !empty($post['name']            );
	$validation_status['description']  = !empty($post['description']     );
	$validation_status['purpose']      = !empty($post['purpose']         );
	$validation_status['bank-account'] = validateIBAN($post['bank-account']);
	$validation_status['ticket']       = validateTicket($file);
	$validation_status['accept-tos']   = $post['accept-tos'];

	return $validation_status;
}

// Transnumerate A..Z in an IBAN to 10..35 (case insensitive)
function transnumerate($input) {
	$result = "";

	foreach ($input as $char) {
		$code = ord($char);

		if ($code >= 48 && $code <= 57) { // ord('0') and ord('9')
			$result .= $char;
		} else { // Character
			$code &= ~32; // Force uppercase
			$result .= (string)$code - 55; // -65, +10 means A (65) becomes 10
		}
	}

	return $result;
}

// Validate IBAN using regexp
function validateIBAN($IBAN) {
	// Validate layout
	if(!preg_match('/([a-zA-Z]{2}[0-9]{2})([a-zA-Z]{4}[0-9]{10})/', $IBAN, $matches))
		return false;

	// Move country code and checksum to end
	$transposed = $matches[2] . $matches[1];

	// Transliterate into numbers, where A = 10, B = 11 .. Z = 35
	$transliterated = transnumerate(str_split($transposed));

	return bcmod($transliterated, "97") == "1"; // Magic IBAN constants
}

function refill($field) {
	if (isset($_POST[$field]))
		return 'value="'. $_POST[$field] . '"';
	return "";
}

// Validate email using the built in PHP
// filter. Does not support TLD addresses.
function validateEmail($email) {
	return filter_var($email, FILTER_VALIDATE_EMAIL)
			&& preg_match('/@.+\./', $email);
}

// Validates wheter the uploaded file is of
// the correct type, size and has the correct
// extension.
function validateTicket($file) {
	// The ticket failed validation if there are
	// any errors with it.
	if ($file['error'] > 0) {
		return false;
	}

	// The original file extension is the last item
	// of the array of the original filename split
	// by periods.
	global $allowed_filetypes;

	// Check the mime type from the file itself
	$mimetype = new finfo(FILEINFO_MIME_TYPE);
	$extension = array_search(
        	$mimetype->file(
			$file['tmp_name']),
		$allowed_filetypes,
        	true
    	);

	if(!$extension){
		return false;
	}

	// Globalify the extension, this should be improved, cause it is ugly.
	global $extension;

	// A file is valid if it has an allowed extension,
	// an allowed filetype and is less than or equal
	// in size to the maximum filesize.
	if($file['size'] <= FILE_MAX_FILESIZE ){
		return true;
	}
	return false;
}

// Validates whether the amount is numeric.
// Since the PHP is_numeric returns false on
// numbers with a comma instead of a dot to
// seperate decimals, we need a function instead
// of using the builtin.
function validateAmount($amount) {
	$amount = str_replace(',', '.', $amount);
	return is_numeric($amount);
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
	// Build the message body
	$mail_body      =
		"Hoi Yorick,\n\n" .

		"Ik heb zojuist het DigiDecs formulier ingevuld. " .
		"Dit zijn mijn gegevens.\n\n" .

		"Naam: {$post['name']}\n" .
		"Email: {$post['email']}\n" .
		"Woonplaats: {$post['city']}\n" .
		"Datum aankoop: {$post['date']}\n" .
		"Totaalbedrag: {$post['totalamount']}\n" .
		"Wat: {$post['description']}\n" .
		"Waarvoor: {$post['purpose']}\n" .
		"Rekeningnummer: {$post['bank-account']}\n" .
		"Opmerkingen: {$post['remarks']}\n\n" .

		"Groetjes,\n\n" .

		"{$post['name']}";

	// Rename the file
        global $allowed_filetypes;

        // Check the mime type from the file itself
        $mimetype = new finfo(FILEINFO_MIME_TYPE);
        $extension = array_search(
                $mimetype->file(
                        $file['tmp_name']),
                $allowed_filetypes,
                true
        );


	$file_name = $file['tmp_name'] . '.' . $extension;
	rename( $file['tmp_name'], $file_name );

	// Create new Mailgun mailer instance and
	// configure the class for sending
	// through Mailgun
	$mgClient = new Mailgun(EMAIL_API_KEY);
	$mgClient->sendMessage(EMAIL_DOMAIN, array(
		'from'    	=> $post['email'],
		'to'      	=> EMAIL_TO_ADDRESS,
		'subject' 	=> EMAIL_SUBJECT_BASE . $post['purpose'],

		'o:tracking' 	=> false,
                'o:tag'         => array('digidecs'),

                'text'          => $mail_body
        ), array(
                'attachment'    => array( $file_name ))
        );
}
