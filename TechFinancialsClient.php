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

    public function __destruct(){
        if ($this->socket){
            fclose($this->socket);
        }
    }

 
    public function validateCustomer($username, $password){

        $this->openSocket();

        $in = "O," . trim($this->apiUsername) . "," . trim($this->apiPassword) . static::STRING_END . "\r\n";
        $data = $this->readFromSocket($in);

        $in = "A,". intval($data[2]) . "," . intval($data[1]) . "," . trim($username) . "," . trim($password) . static::STRING_END . "\r\n";
        $data = $this->readFromSocket($in);



        if (!(strlen($data[4]) < 1)){
            $in = "MS,". intval($data[1]) . "," . $data[4] . static::STRING_END . "\r\n";
            $data = $this->readFromSocket($in);
        }
        return $data;

    }

    private function openSocket(){

        $this->socket = fsockopen("ssl://46.166.130.213", 7777, $errno, $errstr);
        if (!$this->socket) {
            throw new CException("Socket failed. Reason: " . $errstr);
        }

    }


    private function readFromSocket($in, $debug=false){
        fwrite($this->socket, $in);
        while($this->socket)
        {
            $response = fgets($this->socket, 10240);
            $messages = explode(";", $response);
            if($debug)
            {
                return $messages;
            }
            foreach ($messages as $message) {
                $parted=explode(",", $message);
                if($parted[0]=="OR") return $parted;
                if($parted[0]=="R") return $parted;
                if($parted[0]=="MR") return $parted;

            }

        }

    }
 
}