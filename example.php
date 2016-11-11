<?php

include("src/SmtpEmailValidation.php");
include("src/SmtpSocket.php");

$response = SmtpEmailValidation::test('user@domain.from', 'user@domain.to');
echo "<pre>".print_r($response, true)."</pre>";
