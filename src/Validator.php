<?php
namespace webO3\SmtpEmailValidation;

class Validator
{

    /**
     * Test SMTP for a particular email
     *
     * @param string $from
     *            The email in FROM: sent to the SMTP server
     *
     * @param string $to
     *            The email in TO: sent to the SMTP server that is validated
     *
     * @param integer $maxConnTime
     *            The maximum connection timeout of the script
     *
     * @return mixed
     */
    public static function test($from, $to, $maxConnTime = 30)
    {
        set_time_limit($maxConnTime + 1);

        $debug = array();

        // Find the domain name
        list ($fromUser, $fromDomain) = explode('@', $from);
        list ($toUser, $toDomain) = explode('@', $to);
        $mxs = self::getMX($toDomain);

        // last fallback is the original domain
        if (empty($mxs)) {
            return array(
                'code' => 10,
                'success' => false,
                'msg' => 'Could not find any MX record associated with the domain.'
            );
        }

        $timeout = $maxConnTime / count($mxs);

        // Verify MX connexion
        foreach ($mxs as $hostname => $priority) {
            $sock = new SmtpSocket($hostname, $timeout);
            if ($sock->open()) {
                break;
            }
        }

        if (! $sock->isOpen()) {
            return array(
                'code' => 15,
                'success' => false,
                'msg' => 'Could not connect to any MX servers, they maybe down.'
            );
        }

        $reply = $sock->read();
        preg_match('/^([0-9]{3}) /ims', $reply, $matches);
        $code = isset($matches[1]) ? $matches[1] : '';

        if ($sock->hasTimeout()) {
            $response = array(
                'code' => 20,
                'success' => false,
                'msg' => 'Server connexion timeout. They maybe connexion protection in place.'
            );
        } elseif ($code != '220') {
            $response = array(
                'code' => 25,
                'success' => false,
                'msg' => 'Server did not return a 220 response code.'
            );
        } else {
            // say helo
            $sock->write("HELO " . $fromDomain)->read();

            // tell of sender
            $sock->write("MAIL FROM: <" . $from . ">")->read();

            // ask of recepient
            $reply = $sock->write("RCPT TO: <" . $to . ">")->read();
            if ($sock->hasTimeout()) {

                $response = array(
                    'code' => 20,
                    'success' => false,
                    'msg' => 'Server connexion timeout.'
                );
            } else {

                // get code and msg from response
                preg_match('/^([0-9]{3}) /ims', $reply, $matches);
                $code = isset($matches[1]) ? $matches[1] : '';

                if ($code == '250') {
                    $response = array(
                        'code' => $code,
                        'success' => true,
                        'msg' => 'Email is valid'
                    );
                } elseif ($code == '451' || $code == '452') {
                    $response = array(
                        'code' => $code,
                        'success' => true,
                        'msg' => 'The server rejected the email temporary, this indicate that greylisting is in use.'
                    );
                } else {
                    $response = array(
                        'code' => $code,
                        'success' => false,
                        'msg' => "The server rejected the email address."
                    );
                }
            }
        }

        // Set debug data
        $response['debug'] = $sock->debug;

        // Quit and close
        $sock->write("quit")->close();

        return $response;
    }

    /**
     * Query DNS server for MX entries
     *
     * @param string $domain
     *            The domain to get MX from
     *
     * @return array of mx
     */
    public static function getMX($domain)
    {
        $hosts = array();
        $mxweights = array();
        getmxrr($domain, $hosts, $mxweights);

        $mxs = array_combine($hosts, $mxweights);
        asort($mxs, SORT_NUMERIC);

        return $mxs;
    }
}