smtp-email-validation
------------------

This is a PHP class to validate if an email address exists on the remote SMTP server. When you are sending multiple emails, this could save you to send
email to bad email address before they bounce.

* Require PHP with fsockopen and getmxrr and to be able to connect to remote port 25

### How this works:

```php
include("vendor/autoload.php");

$response = \webO3\SmtpEmailValidation\Validator::test('user@domain.from', 'user@domain.to');
echo "<pre>".print_r($response, true)."</pre>";
```

We first check get the MX for the email that we are trying to send to, then we connection to the MX founds one after another by priority to verify if we can connect to.
Then we try to simulate a true SMTP connection by sending a command to the server to see what the server will respond.

We will receive response like this one :


```php
Array
(
    [code] => 10
    [success] => false
    [msg] => Could not find any MX record associated with the domain.
)

Array
(
    [code] => 15
    [success] => false
    [msg] => Could not connect to any MX servers, they maybe down.
)

Array
(
    [code] => 20,
    [success] => false
    [msg] => Server connexion timeout. They maybe connexion protection in place.
)

Array
(
    [code] => 25,
    [success] => false
    [msg] => Server did not return a 220 response code.
)

Array
(
    [code] => 250,
    [success] => true
    [msg] => Email is valid.
)

Array
(
    [code] => 451 ou 452,
    [success] => true
    [msg] => The server rejected the email temporary, this indicate that greylisting is in use.
)

Array
(
    [code] => 451 ou 452,
    [success] => false
    [msg] => The server rejected the email address.
)
```

### Warning:

Many hosting provider or ISP will not allow you to connect directly on remote port 25

You need to set the from address to an address that you will use to send the email, keep in mind that some a blacklist may be in place for particular FROM email address.
