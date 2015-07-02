<?php

class GoogleMapsPlaceDetails extends \GoogleMaps
{
    const PLACE_DETAILS = 'https://maps.googleapis.com/maps/api/place/details/'; //Place Datails Base URL

    public $parameters = array(
    //REQUIRED PARAMETERS
                            'required' => array(
                                'placeid' => null,
    /* A textual identifier that uniquely identifies a place, returned from a Place Search.
     * For more information about place IDs, see the place ID overview. https://developers.google.com/places/place-id */
                                'reference' => null),
    /* A textual identifier that uniquely identifies a place, returned from a Place Search.
     * Note: The reference is now deprecated in favor of placeid. See the deprecation notice on this page. */
    //OPTIONAL PARAMETERS
                            'optional' => array(
                                'extensions' => null,
    /* Indicates if the Place Details response should include additional fields.
     * Additional fields may include premium data, requiring an additional license, or values that are not commonly requested.
     * Extensions are currently experimental. Supported values for the extensions parameter are:
            * review_summary includes a rich and concise review curated by Google's editorial staff. */
                                'language' => null));
    /* The language code, indicating in which language the results should be returned, if possible.
       See the list of supported languages and their codes: https://developers.google.com/maps/faq#languagesupport.
       Note that we often update supported languages so this list may not be exhaustive. */

    function __construct($parameters_required, array $parameters_optional = array(null), $output = null){
        parent::__construct();

        if(\is_array($parameters_required)){
            if(isset($parameters_required['placeid'])){
                $this->parameters['required']['placeid'] = $parameters_required['placeid'];
            } elseif(isset($parameters_required['reference'])){
                $this->parameters['required']['reference'] = $parameters_required['reference'];
            } else {
                throw new LunchboxException('Nieprawidłowe zainicjowanie metody ' . __METHOD__ . '. Brak wymaganych parametrów "placeid" lub "reference".');
            }}
        elseif(\is_string($parameters_required)){
            $this->parameters['required']['placeid'] = $parameters_required;
        } else {
            throw new LunchboxException('Nieprawidłowe zainicjowanie metody ' . __METHOD__ . '. Nieprawidłowy format wymaganego parametru.');
        }

        $this->parameters['optional']['extensions'] = $parameters_optional['bounds'];
        $this->parameters['optional']['language'] = $parameters_optional['language']; //dodać weryfikację

        if(isset($output))
            $this->output = $output;
    }

    public function createQueryURL(){
        if(!(isset($this->parameters['required']['placeid']) XOR isset($this->parameters['required']['reference']))) {
            throw new LunchboxException('Nieprawidłowe wywoałnie metody:' . __METHOD__ . 'Pola: placeid lub/i reference nie zainicjowane');
        }
        if(isset($this->parameters['required']['placeid'])){
            $this->queryURL = self::PLACE_DETAILS . $this->output . '?' . 'placeid=' . $this->parameters['required']['placeid'];
        } else {
            $this->queryURL = self::PLACE_DETAILS . $this->output . '?' . 'reference=' . $this->parameters['required']['reference'];
        }
        foreach ($this->parameters['optional'] as $name => $parameter) {
            if(isset($parameter)) {
                switch($name){
                    default: $this->queryURL .= '&' . $name . '=' . $parameter;
                }
            }
        $this->queryURL .= '&key=' . $this->getKey();
        }
    }

    public static function isValidID($placeID){ //wypadało by poprawić tę funkcję lub całą klasę, gdyż powtarza ona funkcjonalność rodzica
        if(strlen($placeID) != 27)
            return false;

        if(($credentials = \file_get_contents(parent::KEY_FILE)) === false) {
            throw new LunchboxException("Nie udało się otworzyć pliku " . parent::KEY_FILE);
        }
        $url = self::PLACE_DETAILS . 'json' . '?' . 'placeid=' . $placeID . '&key=' . $credentials;
        if(($googleResponse = \file_get_contents($url)) === false) {
            throw new LunchboxException(__METHOD__ . ": Nie udało się otworzyć pliku.");
        }
        $googleResponse = \json_decode($googleResponse, true);
        if(strcasecmp($googleResponse['status'], 'OK'))
            return false;
        else
            return true;
    }
}

?>