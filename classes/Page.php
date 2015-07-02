<?php

class Page
{
    protected $title;
    protected $author;
    protected $html;

    function __construct($page, $title = 'LunchBox', $author = 'Igor Kowalski')
    {
        $file = 'resources' . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $page . '.html';
        $this->html = \file_get_contents($file);
        if($this->html === false)
            throw LunchboxException('Nie udało się zainicjować klasy ' . __CLASS__ . '. Nie udało się wczytać pliku html - ' . $page . '.');
    }

    function change_CSS_file($file) {

        $this->pageDOM->getElementsByTagName('link')->item(0)->setAttribute('href', 'resources/css/' . $file);
        $this->html = $this->pageDOM->saveHTML();

    }

    public function get_html() {
        return $this->html;
    }

    public function set_attribute($id, $attribute, $value) {


        $xpath = new DOMXPath($this->pageDOM); //tworzę obiekt DOMXPath, dzięki któremu będę mógł wyszukac gałąź o odpowiednim id
        $nodes = $xpath->query("//*[@id=\"{$id}\"]"); //query zwraca obiekt DOMNodeLis
        $node = $nodes->item(0); //$node jest DOMElement. Teoretycznie mamy już gałąź, której potrzebujemy
        $node->setAttribute($attribute, $value);

        //$nodeAttribute = $this->$pageDOM->createAttribute($attribute); //tworzymy atrybut, $nodeAttribute jest obiektem DOMAttr
        //$nodeAttribute->value = $value;
        //$node->appendChild($nodeAttribute);

        $this->html = $this->pageDOM->saveHTML();
    }

    public function remove_node($id) {
        $xpath = new DOMXPath($this->pageDOM);
        $node = $xpath->query("//*[@id=\"{$id}\"]")->item(0);
        $parentNode = $node->parentNode;

        $parentNode->removeChild($node);

        $this->html = $this->pageDOM->saveHTML();
    }

    public function add_new_restaurant($restaurant_name, $restaurant_address, $distance, $menu, $price, $placeID) {
        $xpath = new DOMXPath($this->pageDOM);
        $restaurant_card_node_list = $xpath->query("//*[@class='restaurant_card']");
        $last_restaurant_card = $restaurant_card_node_list->item($restaurant_card_node_list->length - 1);
        $new_restaurant_card = $last_restaurant_card->cloneNode(TRUE); //DOMElement
        //$test = $new_restaurant_card->childNodes[1];

        //var_dump($test->childNodes[5]);


        //$last_restaurant_card->parentNode->appendChild($new_restaurant_card);

        //echo var_dump($new_restaurant_card);

        $new_restaurant_card->childNodes[1]->childNodes[1]->nodeValue = $restaurant_name;
        $new_restaurant_card->childNodes[1]->childNodes[3]->nodeValue = $restaurant_address;
        $new_restaurant_card->childNodes[1]->childNodes[5]->nodeValue = $distance;
        $new_restaurant_card->childNodes[3]->childNodes[1]->childNodes[1]->nodeValue = $menu;
        $new_restaurant_card->childNodes[3]->childNodes[4]->childNodes[1]->nodeValue = $price;
        $new_restaurant_card->childNodes[3]->childNodes[13]->childNodes[1]->setAttribute('href', 'add_menu.php?place=' . $placeID . '&restaurant_name=' . urlencode($restaurant_name));
        //echo var_dump($new_restaurant_card->childNodes[3]->childNodes[13]->childNodes[1]);
        //$new_restaurant_card->childNodes[1]->nodeValue = $restaurant_address;
        //$new_restaurant_card->lastChild->nodeValue = $distance;
        //echo (string) $new_restaurant_card;
        /*
        $new_restaurant_card->lastChild->lastChild->firstChild->setAttribute('href', 'add_menu.php?place=' . $placeID . '&restaurant_name=' . urlencode($restaurant_name));
        */

        $new_restaurant_card->removeAttribute('hidden');
        $last_restaurant_card->parentNode->appendChild($new_restaurant_card);

        $this->html = $this->pageDOM->saveHTML();

    } //w przyszłości trzeba poprawic te funkcję bo ona nie może tak działac

    public function render_all() {
        echo $this->html;
    }
}
?>