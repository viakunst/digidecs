# DigiDecs

This is the source code of a web-form that emails the input
it recieves to the treasurer of the study association I'm
currently a board member at.

It is meant to replace the regular paper administration.
Our members can declare stuff they purchased for the
association through this web form. It is nothing exciting;
I'm releasing this code under the MIT license (see `LICENSE`),
but I doubt there's much here.

Some technical details, for the interested:

 - Uses PHP and the Mailgun for mailing
 - Primitive form of templating.
 - Bootstrap as a CSS framework (yes, it's responsive).

# Using this yourself:

If you want to use this for your own association or similar;
follow these steps:

 1. Clone this repository.
 2. Push to your PHP enabled server.
 3. Change constants in `config.example.php` and rename the file
    to `config.php`.
 4. Change/rename templates to your language (currently it's Dutch,
    all variables/names are in English).

Let me know if you end up using parts of this! I'm curious
to see whether I should open source similar stuff in the
future.

[bsdp]:https://github.com/eternicode/bootstrap-datepicker
