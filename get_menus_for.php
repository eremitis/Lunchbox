<?php
include_once 'functions.php';
spl_autoload_register('class_autoloader');

$response['status'] = 'ERROR';
//Predict that somethig goes wrong. If everything is okey, this flag will be change to OK.
$ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') === 0 ? true : false;
/*AJAX Flag. If request comes from AJAX it is set to true, if comes from JS disabled sites fallback, flag is set to false. */

function print_results($response, $ajax = false){
    if($ajax){
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    else {
        $page = new PageDOM('index');
        $page->linkCSS('styles');
        $page->linkCSS('jquery-ui.min', 'vendors/jquery_ui/');
        $page->linkCSS('default', 'vendors/pickadate.js/');
        $page->linkCSS('default.date', 'vendors/pickadate.js/');
        $page->linkCSS('default.time', 'vendors/pickadate.js/');
        $page->linkJS('jquery-1.11.3.min.js', '//code.jquery.com/');
        $page->linkJS('jquery-ui.min.js', 'vendors/jquery_ui/');
        $page->linkJS('picker.js', 'vendors/pickadate.js/');
        $page->linkJS('picker.date.js', 'vendors/pickadate.js/');
        $page->linkJS('picker.time.js', 'vendors/pickadate.js/');
        $page->linkJS('pl_PL.js', 'vendors/pickadate.js/translations/');
        $page->linkJS('js?v=3.exp&signed_in=true&libraries=places', 'https://maps.googleapis.com/maps/api/');
        $page->add_element(script, "var userLocation = {coordinates: {latitude: {$response['lat']}, longitude: {$response['lng']}}, address: '{$response['address']}'};", '//html/head');
        $page->linkJS('lunchbox.main.js');

        if($response['status'] === 'ERROR'){
            //echo var_dump($response);
            $page->add_error($response['description']);
            $page->render_all();
        } else {
            if($response['todays_menus'] !== null){
                $page->remove_node('zero_menus');
                foreach ($response['todays_menus'] as $todays_menu){
                    $page->add_new_restaurant($todays_menu['name'], $todays_menu['vicinity'], $todays_menu['distance'], $todays_menu['place_id'], $todays_menu['menu']['description'], $todays_menu['menu']['price'], $todays_menu['menu']['hours'], 'todays_menus');
                }
            } else {
                $page->remove_attribute('todays_menus', 'hidden');
            }
            if($response['tomorrows_menus'] !== null){
                foreach ($response['tomorrows_menus'] as $tommorows_menu){
                    $page->add_new_restaurant($tommorows_menu['name'], $tommorows_menu['vicinity'], $tommorows_menu['distance'], $tommorows_menu['place_id'], $tommorows_menu['menu']['description'], $tommorows_menu['menu']['price'], $tommorows_menu['menu']['hours'], 'tomorrows_menus');
                }
            }
            if($response['no_menu'] !== null){
                foreach ($response['no_menu'] as $no_menu){
                    $page->add_new_restaurant($no_menu['name'], $no_menu['vicinity'], $no_menu['distance'], $no_menu['place_id']);
                }
            }
            $page->set_attribute('body', 'class', 'start_without_geolocation');
            $page->set_attribute('search_box', 'class', 'afterGPS');
            $page->remove_node('initial_status_box');
            $page->render_all();
        }
    }
}
//CLEANING INPUT
//============================================================
foreach($_POST as &$value){
    $value = trim($value);
}
//Trim all posted variables
//============================================================
//$_GET VARIABLES VALIDATION
//============================================================
$address = filter_input(INPUT_GET, 'address', FILTER_SANITIZE_STRIPPED);
if($ajax){
    $latitude = filter_input(INPUT_GET, 'latitude', FILTER_VALIDATE_FLOAT);
    $longitude = filter_input(INPUT_GET, 'longitude', FILTER_VALIDATE_FLOAT);
} else {
    if($address == false){
        $response['description'] = 'Brak parametru adres. Spróbuj ponownie.';
        print_results($response, $ajax);
    }
    $required['address'] = $address;
    $geocoding = new GoogleMapsGeocoding($required);
    $geocoding->createQueryURL();
    $geocoding->queryGoogle();
    if ($geocoding->googleResponse['status'] === 'OK'){
        $latitude = $geocoding->googleResponse['results'][0]['geometry']['location']['lat'];
        $longitude = $geocoding->googleResponse['results'][0]['geometry']['location']['lng'];
    } else {
        $response['description'] = 'Nie udało się zdekodować adresu na współrzedne. Spróbuj ponownie, a jeżeli problem będzie dalej występować poinformuj nas o zaistniałym problemie';
        print_results($response, $ajax);
    }
}


//============================================================
if($latitude == false || $longitude == false){
    $response['description'] = 'Współrzędne w nieprawidłowym formacie. Spróbuj ponownie lub wpisz adres ręcznie';
    print_results($response, $ajax);
}

$required['location'] = $latitude . ',' . $longitude;
$required['radius'] = 1000;
//Initializing variables required by GoogleMapsNearbySearch object
$optional['rankBy'] = 'distance';
$optional['types'] = array('restaurant');
//Initializing optional variables used by GoogleMapsNearbySearch object

$nearbySearch = new GoogleMapsNearbySearch($required, $optional);
//Initiliazing Google Maps Nearby Search object
$nearbySearch->createQueryURL();
//Create request URL
try{
    $nearbySearch->queryGoogle();
} catch (LunchboxException $e){
    $response['description'] = 'Nie udało się połączyć z serwerami Google Maps. Spróbuj ponownie później, a jeżeli problem będzie dalej występować, poinformuj nas o zaistniałym problemie.';
    print_results($response, $ajax);
}
//Send request to Google Maps Web API

if($nearbySearch->googleResponse['status'] === 'ZERO_RESULTS') {
    $response['description'] = 'Nie znaleziono lokali dla podanej lokalizacji. Spróbuj wpisać inny adres.';
    print_results($response, $ajax);
} elseif ($nearbySearch->googleResponse['status'] !== 'OK') {
    $response['description'] = 'Wystąpił problem. Spróbuj ponownie później, a jeżeli problem będzie dalej występować daj nam o tym znać.';
    print_results($response, $ajax);
}
//Check if request return any results
$restaurants = $nearbySearch->googleResponse['results'];
//Put results into $restaurants variable
$sql_todays_menus = <<<"TODAYSMENUS"
SELECT *, date_format(valid_from, '%k:%i') as valid_from_hour, date_format(valid_to, '%k:%i')as valid_to_hour FROM menu
WHERE place_id = :place_id
AND DATE(valid_from) <= CURDATE()
AND valid_to > NOW();
TODAYSMENUS;
//SQL Template to retrive todays menus
$sql_tomorrows_menus = <<<"TOMMOROWSMENUS"
SELECT *, date_format(valid_from, '%k:%i') as valid_from_hour, date_format(valid_to, '%k:%i')as valid_to_hour FROM menu
WHERE place_id = :place_id
AND DATE(valid_from) <= DATE(NOW() + INTERVAL 1 DAY)
AND DATE(valid_to) >= DATE(NOW() + INTERVAL 1 DAY);
TOMMOROWSMENUS;
//SQL Template to retrive tomorrows menus
try {
    $dbh = new LunchboxPDO();
    $today_statement = $dbh->prepare($sql_todays_menus);
    $tomorrow_statement = $dbh->prepare($sql_tomorrows_menus);
} catch (LunchboxException $e) {
    $response['description'] = 'Nie udało się pobrać aktualnych menu z bazy danych. Spróbuj później ponownie, a jeżeli problem będzie dalej występować daj nam o tym znać.';
    print_results($response, $ajax);
}
//Connect to DB
foreach ($restaurants as $restaurant) {
    $today_statement->execute(array(':place_id' => $restaurant['place_id']));
    $tomorrow_statement->execute(array(':place_id' => $restaurant['place_id']));
    if($row = $today_statement->fetch()){
        $restaurant['menu']['valid_from'] = $row['valid_from'];
        $restaurant['menu']['valid_to'] = $row['valid_to'];
        $restaurant['menu']['valid_from_hour'] = $row['valid_from_hour'];
        $restaurant['menu']['valid_to_hour'] = $row['valid_to_hour'];
        $restaurant['menu']['hours'] = $row['valid_from_hour'] . ' - ' . $row['valid_to_hour'];
        $restaurant['menu']['description'] = $row['description'];
        $restaurant['menu']['price'] = $row['price'];
        $restaurant['distance'] = computeDistance($restaurant['geometry']['location']['lat'], $restaurant['geometry']['location']['lng'], $latitude, $longitude);
        $response['todays_menus'][] = $restaurant;
    } else {
        $restaurant['distance'] = computeDistance($restaurant['geometry']['location']['lat'], $restaurant['geometry']['location']['lng'], $latitude, $longitude);
        $response['no_menu'][] = $restaurant;
    }
    if($row = $tomorrow_statement->fetch()){
        $restaurant['menu']['valid_from'] = $row['valid_from'];
        $restaurant['menu']['valid_to'] = $row['valid_to'];
        $restaurant['menu']['valid_from_hour'] = $row['valid_from_hour'];
        $restaurant['menu']['valid_to_hour'] = $row['valid_to_hour'];
        $restaurant['menu']['hours'] = $row['valid_from_hour'] . ' - ' . $row['valid_to_hour'];
        $restaurant['menu']['description'] = $row['description'];
        $restaurant['menu']['price'] = $row['price'];
        $restaurant['distance'] = computeDistance($restaurant['geometry']['location']['lat'], $restaurant['geometry']['location']['lng'], $latitude, $longitude);
        $response['tomorrows_menus'][] = $restaurant;
    }
}
if($response['todays_menus'] !== null){
    usort($response['todays_menus'], 'sort_by_distance');
}
if($response['tomorrows_menus'] !== null){
    usort($response['tomorrows_menus'], 'sort_by_distance');
}
if($response['no_menu'] !== null){
    usort($response['no_menu'], 'sort_by_distance');
}
//echo var_dump($response);

$response['status'] = 'OK';
$response['lat'] = $latitude;
$response['lng'] = $longitude;
$response['address'] = $address;
print_results($response, $ajax);
?>