<?php

class PageDOM
{
    protected $title;
    protected $author;
    protected $html;
    protected $pageDOM; //Holds DOMDocument with loaded html file

    function __construct($page, $title = 'LunchBox', $author = 'Igor Kowalski')
    {
        libxml_use_internal_errors (true);
        $this->pageDOM = new DOMDocument('1.0', 'UTF-8');
        $this->pageDOM->loadHTMLFile('resources/html/' . $page . '.html', LIBXML_NOENT);
        $this->pageDOM->formatOutput = TRUE;
        $titleNode = $this->pageDOM->getElementsByTagName('title')->item(0);
        $titleNode->nodeValue = $title;

        $this->html = $this->pageDOM->saveHTML();
    }

    public function linkCSS($file, $path = 'resources/css/'){
        $css = $this->pageDOM->createElement('link'); //$css is DOMElement object
        $css->setAttribute('href', $path . $file . '.css');
        $css->setAttribute('rel', 'stylesheet');
        $this->pageDOM->getElementsByTagName('head')->item(0)->appendChild($css);
        $this->html = $this->pageDOM->saveHTML();
    }

    public function linkJS($file, $path = 'resources/js/'){
        $js = $this->pageDOM->createElement('script'); //$js is DOMElement object
        $js->setAttribute('src', $path . $file);
        $this->pageDOM->getElementsByTagName('head')->item(0)->appendChild($js);
        $this->html = $this->pageDOM->saveHTML();
    }

    public function get_html() {
        return $this->html;
    }

    public function set_attribute($id, $attribute, $value) {
        $xpath = new DOMXPath($this->pageDOM); //$xpath is DOMXPath object
        $nodes = $xpath->query("//*[@id=\"{$id}\"]"); //query returns DOMNodeList object
        $node = $nodes->item(0); //$node is DOMElement
        $node->setAttribute($attribute, $value);

        //$nodeAttribute = $this->$pageDOM->createAttribute($attribute); //tworzymy atrybut, $nodeAttribute jest obiektem DOMAttr
        //$nodeAttribute->value = $value;
        //$node->appendChild($nodeAttribute);

        $this->html = $this->pageDOM->saveHTML();
    }

    public function remove_attribute($id, $attribute) {
        $xpath = new DOMXPath($this->pageDOM); //$xpath is DOMXPath object
        $nodes = $xpath->query("//*[@id=\"{$id}\"]"); //query returns DOMNodeList object
        $node = $nodes->item(0); //$node is DOMElement
        $node->removeAttribute($attribute);

        $this->html = $this->pageDOM->saveHTML();
    }

    public function remove_node($id) {
        $xpath = new DOMXPath($this->pageDOM);
        $node = $xpath->query("//*[@id=\"{$id}\"]")->item(0);
        $parentNode = $node->parentNode;
        $parentNode->removeChild($node);
        $this->html = $this->pageDOM->saveHTML();
    }

    public function add_element($element, $value, $append_to) {
        $xpath = new DOMXPath($this->pageDOM);
        $parent = $xpath->query($append_to)->item(0);
        $new_element = $this->pageDOM->createElement($element, $value);
        $parent->appendChild($new_element);
    }

    public function add_new_restaurant($restaurant_name, $restaurant_address, $distance, $placeID, $menu = 'Brak menu na dzisiejszy dzień.', $price = '',  $hours = '', $append_to = 'no_menu') {
        $xpath = new DOMXPath($this->pageDOM);
        $last_restaurant_card = $xpath->query("//*[@class='restaurant_card'][last()]")->item(0);
        $new_restaurant_card = $last_restaurant_card->cloneNode(TRUE); //$new_restaurant_card is DOMElement
        $restaurant_name_node = $xpath->query(".//*[@class='restaurant_name']", $new_restaurant_card)->item(0);
        $restaurant_address_node = $xpath->query(".//*[@class='restaurant_address']", $new_restaurant_card)->item(0);
        $distance_node = $xpath->query(".//*[@class='restaurant_distance_to']", $new_restaurant_card)->item(0);
        $menu_description_node = $xpath->query(".//*[@class='restaurant_menu']", $new_restaurant_card)->item(0);
        if($price){
            $menu_price_node = $xpath->query(".//*[@class='menu_price_text']", $new_restaurant_card)->item(0);
            $price .= ' zł';
        }
        else
            $menu_price_node = $xpath->query(".//*[@class='menu_price']", $new_restaurant_card)->item(0);
        if($hours)
            $menu_hours_node = $xpath->query(".//*[@class='valid_hours_text']", $new_restaurant_card)->item(0);
        else
            $menu_hours_node = $xpath->query(".//*[@class='valid_hours']", $new_restaurant_card)->item(0);
        $place_id = $xpath->query(".//*[@class='add_menu_anchor']", $new_restaurant_card)->item(0);
        $place_details = $xpath->query(".//*[@class='place_details_anchor']", $new_restaurant_card)->item(0);
        $new_restaurant_card->removeAttribute('hidden');
        $restaurant_name_node->nodeValue = $restaurant_name;
        $restaurant_address_node->nodeValue = $restaurant_address;
        $distance_node->nodeValue = $distance . ' km';
        $new_menu_node = $this->pageDOM->createDocumentFragment();
        $new_menu_node->appendXML($menu);
        $menu_description_node->nodeValue = '';
        $menu_description_node->appendChild($new_menu_node);
        $menu_price_node->nodeValue = $price;
        $menu_hours_node->nodeValue = $hours;
        $place_id->setAttribute('href', 'add_menu.php?place_id=' . $placeID . '&restaurant_name=' . urlencode($restaurant_name));
        $place_details->setAttribute('href', 'place_details.php?place_id=' . $placeID);
        $section = $xpath->query("//*[@id='{$append_to}']")->item(0);
        $section->appendChild($new_restaurant_card);
        $section->removeAttribute('hidden');

        $this->html = $this->pageDOM->saveHTML();

    }

    public function add_error($description){
        $xpath = new DOMXPath($this->pageDOM);
        $error_box = $xpath->query("//*[@id='error_box']")->item(0);
        $new_error = $this->pageDOM->createElement('p', $description);
        $error_box->appendChild($new_error);
    }

    public function render_all() {
        echo $this->html;
    }
}
?>