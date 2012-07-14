# PSWinCom Bundle

PSWinCom Bundle is a minimal php port/variant of the [PSWINCom](https://github.com/voldern/pswincomgem) ruby gem library.

This library supports the minimal purpose of sending sms.


## Usage

A simple example

	use PSWinCom\PSWinCom; // Load PSWinCom 
	
	// 1. Set options
	$options = array();
	$options['api_host'] = 'http://sms3.pswin.com/sms'; // set the host default is `http://sms.pswin.com/sms` 
	$options['username'] = "your_username";
    $options['password'] = "your_password";
    $options['sender']   = "SenderName/PhoneNumber";

	// 2. Create a new instance
	$pswin = new PSWinCom($options); 
	
	// 3. Send sms
	$phone   = '4712345678';
	$message = 'Hello World';
	$pswin->sendSms($phone, $message);


### Other global options are:

	`debug_mode`: If set to true message will not be sent, default value false
	`country_code`: The country code to prefix phone numbers with, if the phone number is not prefixed with a country code it will be append if set. There is no default coutry code set.



## Using with Symfony2

Add the following to you deps file.

	[PSWinCom]
	    git=http://github.com/cristobal/pswincom-bundle
	    target=pswincom

And then run `php bin/vendors install --reinstall`

### Autoloading

Append the following to your `app/autoload.php` file to make sure the `PSWinCom` namespace gets registered.

	$loader->registerNamespaces(array(			
		…
		'PSWinCom' => __DIR__.'/../vendor/pswincom/src/', // required to use the PSWinCom library
	));