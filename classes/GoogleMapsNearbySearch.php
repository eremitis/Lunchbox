<?php

class GoogleMapsNearbySearch extends \GoogleMaps
{
    const NBSURL = 'https://maps.googleapis.com/maps/api/place/nearbysearch/'; //Nearby Search Base URL

    public $parameters = array(
    //REQUIRED PARAMETERS
                            'required' => array(
                                'location' => null,
    // The latitude/longitude around which to retrieve place information. This must be specified as latitude,longitude.
                                'radius' => null),
    /* Defines the distance (in meters) within which to return place results. The maximum allowed radius is 50 000 meters.
    Note that radius must not be included if rankby=distance (described under Optional parameters below) is specified. */

    //OPTIONAL PARAMETERS
                            'optional' => array(
                                'keyword' => null,
    /* A term to be matched against all content that Google has indexed for this place, including but not limited to name,
    type, and address, as well as customer reviews and other third-party content. */
                                'language' => null,
    /* The language code, indicating in which language the results should be returned, if possible.
    See the list of supported languages and their codes: https://developers.google.com/maps/faq#languagesupport.
    Note that we often update supported languages so this list may not be exhaustive. */
                                'minPrice' => null,
                                'maxPrice' => null,
    /* (optional) — Restricts results to only those places within the specified range.
    Valid values range between 0 (most affordable) to 4 (most expensive), inclusive.
    The exact amount indicated by a specific value will vary from region to region. */
                                'name' => null,
    /* One or more terms to be matched against the names of places, separated with a space character.
    Results will be restricted to those containing the passed name values.
    Note that a place may have additional names associated with it, beyond its listed name.
    The API will try to match the passed name value against all of these names.
    As a result, places may be returned in the results whose listed names do not match the search term, but whose associated names do.*/
                                'openNow' => null,
    /* Returns only those places that are open for business at the time the query is sent.
    Places that do not specify opening hours in the Google Places database will not be returned
    if you include this parameter in your query. */
                                'rankBy' => null,
    /* rankby — Specifies the order in which results are listed. Possible values are:
         - prominence (default). This option sorts results based on their importance.
         Ranking will favor prominent places within the specified area.
         Prominence can be affected by a place's ranking in Google's index, global popularity, and other factors.

         - distance. This option sorts results in ascending order by their distance from the specified location.
         When distance is specified, one or more of keyword, name, or types is required. */
                                'types' => array(null),
    /* Restricts the results to places matching at least one of the specified types.
    Types should be separated with a pipe symbol (type1|type2|etc). See the list of supported types:
    https://developers.google.com/places/supported_types */
                                'pageToken' => null,
    /* Returns the next 20 results from a previously run search.
    Setting a pagetoken parameter will execute a search with the same parameters used previously — all parameters
    other than pagetoken will be ignored. */
    //                          'zagatSelected' => null
    /* Add this parameter (just the parameter name, with no associated value) to restrict your search to locations
    that are Zagat selected businesses. This parameter must not include a true or false value.
    The zagatselected parameter is experimental, and is only available to Google Places API for Work customers. */
                                ));

    public function __construct(array $param_required, array $param_optional = array(null), $output = null){
        parent::__construct();

        if(isset($param_required['location']) && isset($param_required['radius'])) {
            $this->parameters['required']['location'] = $param_required['location']; //dodać weryfikację
            $this->parameters['required']['radius'] = $param_required['radius']; //dodać weryfikację
        } else
            throw new LunchboxException('Nieprawidłowe zainicjowanie metody ' . __METHOD__ . '. Brak wymaganego parametru "location" i\lub "radius".');

        $this->parameters['optional']['keyword'] = $param_optional['keyword'];
        $this->parameters['optional']['language'] = $param_optional['language']; //dodać weryfikację
        $this->parameters['optional']['minPrice'] = $param_optional['minPrice']; //dodać weryfikację
        $this->parameters['optional']['maxPrice'] = $param_optional['maxPrice']; //dodać weryfikację
        $this->parameters['optional']['name'] = $param_optional['name'];
        $this->parameters['optional']['openNow'] = isset($param_optional['openNow']) ? true : null;
        $this->parameters['optional']['rankBy'] = $param_optional['rankBy']; //dodać weryfikację
        $this->parameters['optional']['types'] = $param_optional['types']; //dodać weryfikację
        $this->parameters['optional']['pageToken'] = $param_optional['pageToken'];
        $this->parameters['optional']['zagatSelected'] = isset($param_optional['zagatSelected']) ? true : null;

        if(isset($output))
            $this->output = $output;
    }

    public function createQueryURL(){
        if(!isset($this->parameters['required']['location']) || !isset($this->parameters['required']['radius'])) {
            throw new LunchboxException('Nieprawidłowe wywoałnie metody:' . __METHOD__ . 'Pola: location lub/i radius nie zainicjowane');
        }
        $this->queryURL = self::NBSURL . $this->output . '?' . 'location=' . $this->parameters['required']['location'] . '&radius=' . $this->parameters['required']['radius'];
        foreach ($this->parameters['optional'] as $name => $parameter) {
            if (isset($parameter)) {
                switch($name){
                    case 'name': $this->queryURL .= '&' . $name . '=' . urlencode($parameter); break;
                    case 'types': $this->queryURL .= '&' . $name . '=';
                                  $this->queryURL .= is_string($parameter) ? $parameter : implode('|', $parameter); break;
                    case 'keyword': $this->queryURL .= '&' . $name . '=' . urlencode($parameter); break;
//                  case 'zagatSelected': $this->queryURL .= '&'. $name; break;
                    default: $this->queryURL .= '&' . $name . '=' . $parameter;
                }
            }
        }
        $this->queryURL .= '&key=' . $this->getKey();
    }
}

?>