<?php

namespace AlexanderEmelyanov\Techfinacials;


class TechFinancialsClient 
{

    public $apiUrl = '';

    public $apiUsername = '';

    public $apiPassword = '';

    public $apiPort = '';

    CONST STRING_END = ';';

    /**
     * Used socket connection for TechFinancials API
     * @var resource
     */
    private $socket = false;

    public function __construct($apiUrl = '', $apiUsername = '', $apiPassword = '', $apiPort = null){
        $this->init($apiUrl, $apiUsername, $apiPassword, $apiPort);
    }

    public function init($apiUrl = null, $apiUsername = null, $apiPassword = null, $apiPort = null){
        if ($apiUrl){
            $this->apiUrl = $apiUrl;
        }

        if ($apiUsername){
            $this->apiUsername = $apiUsername;
        }

        if ($apiPassword){
            $this->apiPassword = $apiPassword;
        }

        if ($apiPort){
            $this->apiPort = $apiPort;
        }

        return true;
    }

    public function __descruct(){
        if ($this->socket){
            socket_close($this->socket);
        }
    }

 
    public function validateCustomer($username, $password){

        $this->openSocket();

        $in = "O," . trim($this->apiUsername) . "," . trim($this->apiPassword) . static::STRING_END . "\r\n";
        $data = $this->readFromSocket($in);

        $in = "A,". intval($data[2]) . "," . intval($data[1]) . "," . trim($username) . "," . trim($password) . static::STRING_END . "\r\n";
        $data = $this->readFromSocket($in);

        if (strlen($data[5]) < 1){
            return false;
        }

        return $data;
    }

    private function openSocket(){
        // Create TCP socket
        if (($this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            throw new CException("Call socket_create() failed. Reason: " . socket_strerror(socket_last_error()));
        }
        // Connect to remote server
        if (socket_connect($this->socket, $this->apiUrl, $this->apiPort) === false) {
            throw new CException("Call socket_connect() failed. Reason: " . socket_strerror(socket_last_error($this->socket)));
        }
    }

    private function readFromSocket($in){
        socket_write($this->socket, $in, strlen($in));
        $response = '';
        while ($out = socket_read($this->socket, 1, PHP_NORMAL_READ)) {
            $response .= $out;
        }
        return explode(",", $response);;
    }

}