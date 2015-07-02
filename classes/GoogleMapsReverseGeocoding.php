<?php

class GoogleMapsReverseGeocoding extends \GoogleMaps
{
    /*
    Reverse Geocoding (Address Lookup)

    The term geocoding generally refers to translating a human-readable address into a location on a map.
    The process of doing the opposite, translating a location on the map into a human-readable address, is known as reverse geocoding. */

    const GCURL = 'https://maps.googleapis.com/maps/api/geocode/';

    public $parameters = array(
    //REQUIRED PARAMETERS: You must supply one, but not both, of the following parameters in a reverse geocoding request:
                            'required' => array(
                                'latlng' => null,
    /* Either: latlng — The latitude and longitude values specifying the location for which you wish to obtain the closest,
    human-readable address. */
                                'place_id' => null),
    /* Or: place_id — The place ID of the place for which you wish to obtain the human-readable address.
    The place ID is a unique identifier that can be used with other Google APIs.
    For example, you can use the placeID returned by the Google Maps Roads API to get the address for a snapped point.
    For more information about place IDs, see the place ID overview: https://developers.google.com/places/place-id */
    //OPTIONAL PARAMETERS in a reverse geocoding request:
    //These are the optional parameters that you can include in a reverse geocoding request:
        'optional' => array(
            'result_type' => array(null),
    /* One or more address types, separated by a pipe (|). Examples of address types: country, street_address, postal_code.
    For a full list of allowable values, see the address types on this page:
    https://developers.google.com/maps/documentation/geocoding/#Types
    Specifying a type will restrict the results to this type.
    If multiple types are specified, the API will return all addresses that match any of the types.
    Note: This parameter is available only for requests that include an API key or a client ID. */
            'language' => null,
    /* The language in which to return results. See the list of supported domain languages.
    Note that we often update supported languages so this list may not be exhaustive.
    If language is not supplied, the geocoder will attempt to use the native language of the domain
    from which the request is sent wherever possible. */
            'location_type' => 'ROOFTOP'));
    /* One or more location types, separated by a pipe (|). Specifying a type will restrict the results to this type.
    If multiple types are specified, the API will return all addresses that match any of the types.
    Note: This parameter is available only for requests that include an API key or a client ID. The following values are supported:
        - "ROOFTOP" restricts the results to addresses for which we have location information accurate down to street address precision.
        - "RANGE_INTERPOLATED" restricts the results to those that reflect an approximation (usually on a road)
           interpolated between two precise points (such as intersections). An interpolated range generally
           indicates that rooftop geocodes are unavailable for a street address.
        - "GEOMETRIC_CENTER" restricts the results to geometric centers of a location such as a
           polyline (for example, a street) or polygon (region).
        - "APPROXIMATE" restricts the results to those that are characterized as approximate. */
    /* If both result_type and location_type restrictions are present then the API will return only
    those results that matches both the result_type and the location_type restrictions. */

    public function __construct($parameters_required, array $parameters_optional = array(null), $output = null) {
        parent::__construct();

        if(\is_array($parameters_required)){
            if(isset($parameters_required['latlng'])){
                $this->parameters['required']['latlng'] = $parameters_required['latlng'];
            } elseif(isset($parameters_required['place_id'])){
                $this->parameters['required']['place_id'] = $parameters_required['place_id'];
            } else {
                throw new LunchboxException('Nieprawidłowe zainicjowanie metody ' . __METHOD__ . '. Brak wymaganych parametrów "latlng" lub "place_id".');
            }}
        elseif(\is_string($parameters_required)){
            $this->parameters['required']['latlng'] = $parameters_required;
        } else {
            throw new LunchboxException('Nieprawidłowe zainicjowanie metody ' . __METHOD__ . '. Brak wymaganego parametru "latlng" i\lub "place_id".');
        }


        $this->parameters['optional']['result_type'] = $parameters_optional['result_type'];
        $this->parameters['optional']['language'] = $parameters_optional['language']; //dodać weryfikację
        if($parameters_optional[0] !== null)
            $this->parameters['optional']['location_type'] = $parameters_optional['location_type']; //dodać weryfikację

        if(isset($output))
            $this->output = $output;
    }

    public function createQueryURL(){
        if(!(isset($this->parameters['required']['latlng']) XOR isset($this->parameters['required']['place_id']))) {
            throw new LunchboxException('Nieprawidłowe wywoałnie metody:' . __METHOD__ . 'Pola: latlng lub/i place_id nie zainicjowane');
        }
        if(isset($this->parameters['required']['latlng'])){
            $this->queryURL = self::GCURL . $this->output . '?' . 'latlng=' . $this->parameters['required']['latlng'];
        } else {
            $this->queryURL = self::GCURL . $this->output . '?' . 'place_id=' . $this->parameters['required']['place_id'];
        }

        foreach ($this->parameters['optional'] as $name => $parameter) {
            if (isset($parameter)) {
                switch($name){
                    case 'result_type': $this->queryURL .= '&' . $name . '=';
                                       $this->queryURL .= is_string($parameter) ? $parameter : implode('|', $parameter); break;
                    case 'location_type': $this->queryURL .= '&' . $name . '=';
                                       $this->queryURL .= is_string($parameter) ? $parameter : implode('|', $parameter); break;
                    default: $this->queryURL .= '&' . $name . '=' . $parameter;
                }
            }
        }
        $this->queryURL .= '&key=' . $this->getKey();
    }
}

?>