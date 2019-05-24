<?php
namespace webO3\SmtpEmailValidation;

class SmtpSocket
{

    /**
     * SMTP socket hostname
     *
     * @var string
     */
    protected $hostname = "";

    /**
     * SMTP socket port
     *
     * @var integer
     */
    protected $port = 25;

    /**
     * Connection timeout
     *
     * @var integer
     */
    protected $timeout = 30;

    /**
     * Socket resource
     *
     * @var resource
     */
    protected $sock;

    /**
     * Debug array
     *
     * @var array
     */
    public $debug = array();

    /**
     * Constructor
     *
     * @param string $hostname
     * @param integer $timeout
     */
    public function __construct($hostname, $timeout = null)
    {
        $this->hostname = $hostname;

        if ($timeout > 0) {
            $this->timeout = $timeout;
        }
    }

    /**
     * Open socket
     *
     * @return boolean
     */
    public function open()
    {
        if ($this->sock = @fsockopen($this->hostname, $this->port, $errno, $errstr, $this->timeout)) {
            stream_set_timeout($this->sock, $this->timeout);
            return true;
        }

        return false;
    }

    /**
     * Is the socket open ?
     *
     * @return boolean
     */
    public function isOpen()
    {
        return ! ! $this->sock;
    }

    /**
     * Get socket metadata
     *
     * @return array
     */
    protected function getMetaData()
    {
        return stream_get_meta_data($this->sock);
    }

    /**
     * Is the last action timeout ?
     *
     * @return boolean
     */
    public function hasTimeout()
    {
        $info = $this->getMetaData();
        return ! ! $info['timed_out'];
    }

    /**
     * Read from socket
     *
     * @return string
     */
    public function read()
    {
        $data = fread($this->sock, 2082);
        $this->debug[] = '< ' . $data;
        return $data;
    }

    /**
     * Write to socket
     *
     * @param string $data
     * @return SmtpSocket
     */
    public function write($data)
    {
        fwrite($this->sock, $data . "\r\n");
        $this->debug[] = '> ' . $data;
        return $this;
    }

    /**
     * Close socket
     */
    public function close()
    {
        fclose($this->sock);
    }
}