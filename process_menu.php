<?php
require_once 'functions.php';
spl_autoload_register('class_autoloader');

$response['status'] = 'ERROR';
//Predict that somethig goes wrong. If everything is okej, this flag will be change to OK.
$ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') === 0 ? true : false;
/*AJAX Flag. If request comes from AJAX it is set to true, if comes from JS disabled sites fallback, flag is set to false. */

//CLEANING INPUT
//============================================================
foreach($_POST as &$value){
    $value = trim($value);
}
//Trim all posted variables
//============================================================
//BASIC VALIDATION & SANITIZATION
//============================================================
$clean = array(
            'place_name' => filter_input(INPUT_POST, 'restaurant_name', FILTER_SANITIZE_STRING),
    //Sanitizing posted place name. We don't use this variable in script but maybe in future will
            'menu_valid_from' => filter_input(INPUT_POST, 'menu_valid_from', FILTER_VALIDATE_REGEXP, array(
                                                                                                'options' => array(
                                                                                                    'regexp' => '#^\d{4}-(0[1-9]|1[012])-([012]\d|3[01])$#'))),
    //Validates if start date is in correct format (CCYY-MM-DD)
            'menu_valid_to' => filter_input(INPUT_POST, 'menu_valid_to', FILTER_VALIDATE_REGEXP, array(
                                                                                                'options' => array(
                                                                                                    'regexp' => '#^\d{4}-(0[1-9]|1[012])-([012]\d|3[01])$#'))),
    //Validates if end date is in correct format (CCYY-MM-DD)
            'valid_hours_start' => filter_input(INPUT_POST, 'valid_hours_start', FILTER_VALIDATE_REGEXP, array(
                                                                                                'options' => array(
                                                                                                    'regexp' => '#^([01]\d|2[0123]):[012345]\d$#'))),
    //Validates if start hours is in correct format (HH:i)
            'valid_hours_end' => filter_input(INPUT_POST, 'valid_hours_end', FILTER_VALIDATE_REGEXP, array(
                                                                                                'options' => array(
                                                                                                    'regexp' => '#^([01]\d|2[0123]):[012345]\d$#'))),
    //Validates if end hours is in correct format (HH:i)
            'menu' => filter_input(INPUT_POST, 'menu', FILTER_SANITIZE_STRING),
    //Sanitizing posted menu
            'price' => filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT),
    //Validates if price is float
            'menu_name' => filter_input(INPUT_POST, 'menu_name', FILTER_SANITIZE_STRING),
    //Sanitizing posted menu name
            'place_id' => filter_input(INPUT_POST, 'place_ID', FILTER_VALIDATE_REGEXP, array(
                                                                                                'options' => array(
                                                                                                    'regexp' => '#^[[:graph:]]{27}$#')))
    //Validates if place_ID has correct length
);
//========================================================
//ADVANCE VALIDATION
//========================================================
$today = new DateTime(); //Holds todays DateTime object
$date_difference; //Holds DateTimeInterval object, difference between $todays and $menu_valid_from or $menu_valid_to
$response['errors'] = null;
//menu_valid_from variable validation. Checks if date and hours pass 'Basic Validation' and also if date is bigger than today and if less than 30 days
if(!$clean['menu_valid_from']){
    $response['errors']['menu_valid_from'] = 'Wprowadzono początkową datę w nieprawidłowym formacie. Prawidłowy format to YYYY-MM-DD (np. 2015-06-23). Popraw datę i spróbuj ponownie.';
}
if(!$clean['valid_hours_start']){
    $response['errors']['valid_hours_start'] = 'Godzina początkowa jest w nieprawidłowym formacie. Prawidłowa postać godziny to HH:MM, czyli np. 23:30. Popraw godzinę i spróbuj ponownie.';
} else {
    $menu_valid_from = new DateTime($clean['menu_valid_from'] . 'T' . $clean['valid_hours_start']);
    $date_difference = $today->diff($menu_valid_from);
    if ($date_difference->invert && $date_difference->days >= 1) {
        $response['errors']['menu_valid_from'] = 'Dodałeś menu dla dnia, który już był. Nasza strona nie obsługuje podróży w czasie. Popraw datę początkową i spróbuj ponownie.';
    } elseif ($date_difference->days > 30) {
        $response['errors']['menu_valid_from'] = 'Przykro mi, ale obslugujemy menu jedynie na najbliższy miesiąc. Popraw datę początkową i spróbuj ponownie.';
    }
}
//menu_valid_to variable validation. Checks if date and hours pass 'Basic Validation' and also if date is bigger than today and if less than 30 days
if(!$clean['menu_valid_to']) {
    $response['errors']['menu_valid_to'] = 'Wprowadzono końcową datę w nieprawidłowym formacie. Prawidłowy format to YYYY-MM-DD (np. 2015-06-24). Popraw datę i spróbuj ponownie.';
}
if(!$clean['valid_hours_end']){
    $response['errors']['valid_hours_end'] = 'Godzina początkowa jest w nieprawidłowym formacie. Prawidłowa postać godziny to HH:MM, czyli np. 23:30. Popraw godzinę i spróbuj ponownie';
} else {
    $menu_valid_to = new DateTime($clean['menu_valid_to'] . 'T' . $clean['valid_hours_end']);
    $date_difference = $today->diff($menu_valid_to);
    if ($date_difference->invert && $date_difference->days >= 1) {
        $response['errors']['menu_valid_to'] = 'Dodałeś menu dla dnia, który już był. Nasza strona nie obsługuje podróży w czasie. Popraw datę końcową i spróbuj ponownie.';
    } elseif ($date_difference->days > 30) {
        $response['errors']['menu_valid_to'] = 'Przykro mi, ale obslugujemy menu jedynie na najbliższy miesiąc. Popraw datę końcową i spróbuj ponownie.';
    }
}
//menu variable validation. Checks if menu is bigger than 10 and smaller than 255 charakters
if($clean['menu'] == false || \strlen($clean['menu']) < 10){
    $response['errors']['menu'] = 'Twoje menu musi składać się z co najmniej 10 znaków. Postaraj się być bardziej opisowy :)';
} elseif(\strlen($clean['menu']) > 255){
    $response['errors']['menu'] = 'Twoje menu nie może zawierać więcej niż 255 znaków. Postaraj się być mniej szczegółowy :)';
} else{
    $menu = nl2br($clean['menu']);
}
//price variable validation. Checks if price is positive
if($clean['price'] == false || $clean['price'] < 0.0){
    $response['errors']['price'] = 'Cena nie może być ujemna. Wprowadź wartość dodatnią i spróbuj ponownie.';
} else{
    $price = $clean['price'];
}
//menu_name variable validation. Checks if name is smaller than 31 charakters
if(\strlen($clean['menu_name']) > 31){
    $response['errors']['menu_name'] = 'Nazwa menu nie może być dłuższa niż 31 znaków. Skróć ją i spróbuj ponownie';
} else {
    $menu_name = $clean['menu_name'];
}
//place_id variable validation. Checks if google has this id in theirs database
if(!GoogleMapsPlaceDetails::isValidID($clean['place_id'])){
    $response['errors']['place_id'] = 'placeID jest nieprawidłowe. Skontaktuj się z nami i poinformuj nas o tym problemie';
} else {
    $place_id = $clean['place_id'];
}
//ADVANCE VALIDATION END
if(!is_null($response['errors'])){
    if($ajax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } /* Add non JavaScript solution
    else {

    } */
}
//If something goes wrong something is in errors array. We send it contents and exit script

//INSERTING INTO DATABASE
$sql_attributes = array(
                    ':place_id' => $place_id,
                    ':valid_from' => $menu_valid_from->format(DateTime::ISO8601),
                    ':valid_to' => $menu_valid_to->format(DateTime::ISO8601),
                    ':description' => $menu,
                    ':price' => $price);

$statment_template = 'INSERT INTO menu VALUES(:place_id, :valid_from, :valid_to, :description, :price)';

try {
    $dbh = new LunchboxPDO();
} catch (LunchboxException $e){
    $response['errors']['db'] = 'Nie udało się połączyć z bazą danych. Spróbuj ponownie za jakiś czas, a jeżeli problem będzie dalej występować, skontaktuj sie z nami i poinformuj nas o tym.';    if($ajax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } /* Add non JavaScript solution
    else {

    } */
}
try {
    $insert_statement = $dbh->prepare($statment_template);
} catch(PDOException $e) {
    $response['errors']['db'] = 'Wystąpił problem z wysłaniem Twoich danych. Spróbuj ponownie za jakiś czas, a jeżeli problem się powtórzy, skontaktuj sie z nami i poinformuj nas o tym.';
    if($ajax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } /* Add non JavaScript solution
        else {

        } */
}
if($insert_statement->execute($sql_attributes)){
    $resonse['status'] = 'OK';
    if($ajax) {
        header('Content-Type: application/json');
        echo json_encode($resonse);
        exit();
    } /* Add non JavaScript solution
    else {

    } */
} else {
    $response['errors']['db'] = 'Nie udało się dodać Twoich danych fo bazy danych. Spróbuj ponownie za jakiś czas, a jeżeli problem się powtórzy, skontaktuj sie z nami i poinformuj nas o tym.';
    if($ajax) {
        header('Content-Type: application/json');
        echo json_encode($resonse);
        exit();
    } /* Add non JavaScript solution
    else {

    } */
}

?>