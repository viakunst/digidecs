	<form role="form" action="index.php" method="post" enctype="multipart/form-data" id="digidecs-form">
		<div class="form-group">
			<label for="name">Naam<sup>*</sup></label>
			<input type="text" class="form-control" id="name" name="name" placeholder="Secretaris"
			<?php echo refill("name"); ?>
			required>
		</div>

                <div class="form-group">
                        <label for="bank-account">IBAN<sup>*</sup></label>
						<input id="bank-account" name="bank-account" type="text" class="form-control" placeholder="NL13TEST0123456789"
						<?php echo refill("bank-account"); ?>
						required>
                </div>

		<div class="form-group">
			<label for="email">E-mailadres<sup>*</sup></label>
			<input type="email" class="form-control" id="email" name="email" placeholder="gigantischebaas@svsticky.nl"
			<?php echo refill("email"); ?>
			required>
		</div>

		<div class="form-group">
			<label for="totalamount">Totaalbedrag<sup>*</sup></label>
			<div class="input-group">
				<span class="input-group-addon">&euro;</span>
				<input id="totalamount" name="totalamount" type="text" class="form-control" placeholder="9999.00" required pattern="[-+]?[0-9]*[.]?[0-9]+" oninvalid="setCustomValidity('Only use digits and separate them with a dot (.) if needed.')"
	onchange="try{setCustomValidity('')}catch(e){}"
				<?php echo refill("totalamount"); ?>>
			</div>
		</div>

		<div class="form-group">
			<label for="description">Wat heb je gekocht?<sup>*</sup></label>
			<input id="description" name="description" type="text" class="form-control" placeholder="Graafmachine"
			<?php echo refill("description"); ?> required>
		</div>

		<div class="form-group">
			<label for="purpose">Waarvoor/welke commissie?<sup>*</sup></label>
			<input id="purpose" name="purpose" type="text" class="form-control" placeholder="Bestuur, lul!"
			<?php echo refill("purpose"); ?> required>
		</div>

		<div class="form-group">
			<label for="ticket">Bonnetje uploaden<sup>*</sup></label>
			<input type="hidden" name="MAX_FILE_SIZE" value="10485760">
			<input name="ticket" type="file" id="ticket" required>
			<p class="help-block">Maximale grootte is 10MB. Alleen .pdf, .jpg, .gif en .png bestanden.</p>

                        <p class="help-block">Zorg dat de datum, het (btw) bedrag en de verschillende producten of diensten goed leesbaar zijn.</p>
		</div>

		<div class="form-group">
			<label for="remarks">Opmerkingen</label>
			<textarea name="remarks" class="form-control" rows="3" type="textarea" id="remarks"><?php if (isset($_POST['remarks'])) echo $_POST['remarks']; ?></textarea>
		</div>

		<div class="form-group">
			<label for="accept-tos">
				<input type="checkbox" name="accept-tos" id="accept-tos" required> Ik heb alles gecheckt en naar waarheid ingevuld<sup>*</sup>
			</label>
		</div>
		<p>Velden met een <sup>*</sup> zijn verplicht</p>
		<input type="submit" class="btn btn-success" value="Geef geld!">
	</form>
</div>
