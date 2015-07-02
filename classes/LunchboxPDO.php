<?php

class LunchboxPDO extends PDO
{
    const LUNCHBOX_PDO_PASS = '..' . DIRECTORY_SEPARATOR . 'credentials' . DIRECTORY_SEPARATOR . 'pdo.pass';
    /*String concetation operator doesn't work in constant definition with < PHP 5.6 */
    private $user;
    private $password;
    private $dsn;

    function __construct($config_file = null){
        if(is_null($config_file)){
            $config_file = $this::LUNCHBOX_PDO_PASS;
        }
        if(($credentials = \file_get_contents($config_file)) === false) {
            throw new LunchboxException($this->message, $this->code, 'FILE');
        }
        list($this->dsn, $this->user, $this->password) = explode('|', $credentials);
        try{
            parent::__construct($this->dsn, $this->user, $this->password);
        } catch (PDOException $exception){
            throw new LunchboxException($this->message, $this->code, 'FILE');
        }
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
}

?>