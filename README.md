smtp-email-validation
------------------

This is a PHP class to validate if an email address exists on the remote SMTP server. When you are sending multiple emails, this could save you to send 
email to bad email address before they bounce.

* Require PHP with fsockopen and getmxrr and to be able to connect to remote port 25

##### How this works:

SmtpEmailValidation::test($from = 'user@domain.from', $to = 'user@domain.to');

We first check get the MX for the email that we are trying to send to, then we connection to the MX founds one after another by priority to verify if we can connect to.
Then we try to simulate a true SMTP connection by sending a command to the server to see what the server will respond.

We will receive response like this one :

Array
(
    [code] => 10
    [success] => 
    [msg] => Could not find any MX record associated with the domain.
)


##### Warning:

Many hosting provider or ISP will not allow you to connect directly on remote port 25

You need to set the from address to an address that you will use to send the email, keep in mind that some a blacklist may be in place for particular FROM email address.
