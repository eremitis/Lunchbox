<?php

abstract class GoogleMaps {
    const KEY_FILE = '..' . DIRECTORY_SEPARATOR . 'credentials' . DIRECTORY_SEPARATOR . 'GoogleMapsWebApiKey.pass';
    /*String concetation operator doesn't work in constant definition with < PHP 5.6 */
    private $key;
    /* Your application's API key. This key identifies your application for purposes of quota
    management and so that places added from your application are made immediately available to your app. */
    protected $output = 'json';
    /* Where output may be either of the following values:
    - json (recommended //by google tutorial) indicates output in JavaScript Object Notation (JSON)
    - xml indicates output as XML */
    //protected $status;
    /* The "status" field contains the status of the request, and may contain debugging information
    to help you track down why the request failed.*/
    public $queryURL;
    /* Holds URL with query to google web services */
    public $googleResponse;

    protected function __construct() {
        if(($credentials = \file_get_contents(self::KEY_FILE)) === false) {
            throw new LunchboxException("Nie udało się otworzyć pliku {$this->key_file}.");
        }
        $this->key = $credentials;
    }

    final protected function getKey() {
        if(isset($this->key)){
            return $this->key;
        }
    }

    final public function queryGoogle(){
        if(($googleResponse = \file_get_contents($this->queryURL)) === false) {
            throw new LunchboxException(__METHOD__ . ": Nie udało się otworzyć pliku.");
        }
        if(\strcasecmp($this->output, 'json') === 0){
            $this->googleResponse = \json_decode($googleResponse, true);
        } elseif(\strcasecmp($this->output, 'xml') === 0){
            $this->googleResponse = \json_decode(\json_encode(new SimpleXMLElement($googleResponse)), true);
        }
        else
            throw new LunchboxException(__METHOD__ . ': Nieobsługiwany rodzaj odpowiedzi.');
    }

    abstract function createQueryURL();

}
?>