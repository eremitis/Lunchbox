<?php

class GoogleMapsGeocoding extends \GoogleMaps {

    const GCURL = 'https://maps.googleapis.com/maps/api/geocode/';

    public $parameters = array(
    //REQUIRED PARAMETERS
                            'required' => array(
                                'address' => null,
    /* The street address that you want to geocode, in the format used by the national postal service of the country concerned.
    Additional address elements such as business names and unit, suite or floor numbers should be avoided.
    Please refer to the FAQ for additional guidance: https://developers.google.com/maps/faq#geocoder_queryformat */
                                'components' => array(null)),
    /* A component filter for which you wish to obtain a geocode. See Component Filtering for more information.
    The components filter will also be accepted as an optional parameter if an address is provided.*/
    //OPTIONAL PARAMETERS
                            'optional' => array(
                                'bounds' => null,
    /* The bounding box of the viewport within which to bias geocode results more prominently.
    This parameter will only influence, not fully restrict, results from the geocoder.
    (For more information see Viewport Biasing - https://developers.google.com/maps/documentation/geocoding/#Viewports.) */
                                'language' => null,
    /* The language in which to return results. See the list of supported domain languages.
    Note that we often update supported languages so this list may not be exhaustive.
    If language is not supplied, the geocoder will attempt to use the native language of the domain
    from which the request is sent wherever possible. */
                                'region' => null,
    /* The region code, specified as a ccTLD ("top-level domain") two-character value.
    This parameter will only influence, not fully restrict, results from the geocoder.
    (For more information see Region Biasing below - https://developers.google.com/maps/documentation/geocoding/#RegionCodes) */
                                'components' => array(null)));
    /* The component filters, separated by a pipe (|).
    Each component filter consists of a component:value pair and will fully restrict the results from the geocoder.
    For more information see Component Filtering - https://developers.google.com/maps/documentation/geocoding/#ComponentFiltering */


    public function __construct($parameters_required, array $parameters_optional = array(null), $output = null) {
        parent::__construct();

        if(\is_array($parameters_required)){
            if(isset($parameters_required['address'])){
                $this->parameters['required']['address'] = $parameters_required['address'];
            } elseif(isset($parameters_required['components'])){
                $this->parameters['required']['components'] = $parameters_required['components'];
            } elseif(\is_string($parameters_required)){
                $this->parameters['required']['address'] = $parameters_required;
            } else{
                throw new LunchboxException('Nieprawidłowe zainicjowanie metody ' . __METHOD__ . '. Brak wymaganego parametru "address" i\lub "components".');
            }
        }

        $this->parameters['optional']['bounds'] = $parameters_optional['bounds'];
        $this->parameters['optional']['language'] = $parameters_optional['language']; //dodać weryfikację
        $this->parameters['optional']['region'] = $parameters_optional['region']; //dodać weryfikację
        $this->parameters['optional']['components'] = $parameters_optional['components']; //dodać weryfikację

        if(isset($output))
            $this->output = $output;
    }

    public function createQueryURL(){
        if(!isset($this->parameters['required']['address']) && !isset($this->parameters['required']['components'])) {
            throw new LunchboxException('Nieprawidłowe wywoałnie metody:' . __METHOD__ . 'Pola: address lub/i components nie zainicjowane');
        }
        if(isset($this->parameters['required']['address'])){
            $this->queryURL = self::GCURL . $this->output . '?' . 'address=' . \urlencode($this->parameters['required']['address']);
        } else {
            if(\is_string($this->parameters['required']['components'])){
                $this->queryURL = self::GCURL . $this->output . '?' . 'components=' . $this->parameters['required']['components'];
            } elseif(\is_array($this->parameters['required']['components'])){
                $this->queryURL = self::GCURL . $this->output . '?' . 'components=' . implode('|', $this->parameters['required']['components']);
            }
        }
        foreach ($this->parameters['optional'] as $name => $parameter) {
            if (isset($parameter)) {
                switch($name){
                    case 'components': $this->queryURL .= '&' . $name . '=';
                                       $this->queryURL .= is_string($parameter) ? $parameter : implode('|', $parameter); break;
                    default: $this->queryURL .= '&' . $name . '=' . $parameter;
                }
            }
        }
        $this->queryURL .= '&key=' . $this->getKey();
    }
}

?>