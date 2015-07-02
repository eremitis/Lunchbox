<?php
require_once 'functions.php';
spl_autoload_register('class_autoloader');

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
$page->linkJS('lunchbox.main.js');
$page->render_all();

?>