if(userLocation === undefined){
	var userLocation = {}; //holds user location (address and Lng/Lat coordinates
}
var today = new Date(); //holds todays date

$(document).ready(function() {
	console.log('Inside ready()!');
	//GETTING USER COORDINATES

	if(navigator.geolocation) {
		if(!($('#body').is('.start_without_geolocation'))) {
			navigator.geolocation.getCurrentPosition(coordinates, geolocationError);
			/*
			GetCurrentPosition method calls 'coordinates' callback function on success and 'geolocationError' on failure.
			'coordinates' callback get Position object with coords.longitude and coords.longitude properties. */
		}
	} else {
		console.log('Notice: Browser doesn\'t support geolocation (navigator.geolocation == false)');
	}

	function checkCoordinatesAndRedraw(){
		if(navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(coordinates, geolocationError);
		} else {
			console.log('Notice: Browser doesn\'t support geolocation (navigator.geolocation == false)');
	   		$('#error_box_text').text('Przykro mi ale Twoja przeglądarka nie wspiera geolokacji, sprawdzenie wyników dla aktualnej lokalizacji jest niemożliwe.');
	   		$('#error_box').fadeIn(800);
		}
	}

	function coordinates(position) {
		$('#loading_icon').attr('src', 'resources/images/icons/svg/ajax_gear.svg');
		$('#search').attr('placeholder', 'Pobieranie obecnej lokalizacji.....');
		userLocation = {
				coordinates: {
					latitude: Math.round(position.coords.latitude * 10000000)/10000000,
					longitude: Math.round(position.coords.longitude * 10000000)/10000000
				},
				address: '',
		};
		/* Google Maps Embed API uses only addresses, so we need to reverse geocode coordinates to get current address. We
		 * will use this address to display user route to chosen place. */

		var geocoder = new google.maps.Geocoder();
		/* Creating Geocoder Object
		 * google.maps.Geocoder class
		 * --------------------------
		 * A service for converting between an address and a LatLng.
		 * Constructor|	Description
		 * Geocoder() |	Creates a new instance of a Geocoder that sends geocode requests to Google servers. */
		var latlng = new google.maps.LatLng(userLocation.coordinates.latitude, userLocation.coordinates.longitude);
		/* Creating LatLng Object. We will use it with 'geocoder' object
		 * google.maps.LatLng class
		 * ------------------------------------------------------------
		 * Constructor		| Description
		 * LatLng(			| Creates a LatLng object representing a geographic point.
		 * lat:number,		| Latitude is specified in degrees within the range [-90, 90].
		 * lng:number,		| Longitude is specified in degrees within the range [-180, 180].
		 * noWrap?:boolean)	| Set noWrap to true to enable values outside of this range. Note the ordering of latitude and longitude.
		 */
		$('#search').attr('placeholder', 'Geokodowanie lokalizacji......');
		geocoder.geocode({location: latlng}, function(results, status) {
		/* geocode method takes 'location' parameter (type LatLng) and callback function which handle results. Callback gets two
		 * arguments 'results' (array of GeocoderResult Objects) and 'status'
		 * ___________________________________________________________________________________________________________________________
		 *|Methods																					 |Return Value|	Description		  |
		 *|geocode(request:GeocoderRequest, callback:function(Array<GeocoderResult>, GeocoderStatus))|None		  |Geocode a request. |
		 *---------------------------------------------------------------------------------------------------------------------------- */
			if(status === google.maps.GeocoderStatus.OK) {
		/* google.maps.GeocoderStatus class
		===================================
		The status returned by the Geocoder on the completion of a call to geocode().

		Constant			Description
		ERROR				There was a problem contacting the Google servers.
		INVALID_REQUEST		This GeocoderRequest was invalid.
		OK					The response contains a valid GeocoderResponse.
		OVER_QUERY_LIMIT	The webpage has gone over the requests limit in too short a period of time.
		REQUEST_DENIED		The webpage is not allowed to use the geocoder.
		UNKNOWN_ERROR		A geocoding request could not be processed due to a server error. The request may succeed if you try again.
		ZERO_RESULTS		No result was found for this GeocoderRequest. */
				userLocation.address = results[0].formatted_address;
		/* google.maps.GeocoderResult object specification
		==================================================
		A single geocoder result retrieved from the geocode server. A geocode request may return multiple result objects.
		Note that though this result is "JSON-like," it is not strictly JSON, as it indirectly includes a LatLng object.

		Properties			Type							Description
		address_components	Array<GeocoderAddressComponent>	An array of GeocoderAddressComponents
		formatted_address	string							A string containing the human-readable address of this location.
		geometry			GeocoderGeometry				A GeocoderGeometry object
		partial_match		boolean							Whether the geocoder did not return an exact match for the original request,
															though it was able to match part of the requested address.
		postcode_localities	Array<string>					An array of strings denoting all the localities contained in a postal code.
															This is only present when the result is a postal code that contains
															multiple localities.
		types				Array<string>					An array of strings denoting the type of the returned geocoded element.
															For a list of possible strings, refer to the Address Component Types
															section of the Developer's Guide.*/
				window.history.pushState({}, 'Lunchbox', 'get_menus_for.php?address=' + encodeURIComponent(userLocation.address));
				console.log('Latitude   [Wysokość]: ' + userLocation.coordinates.latitude);
			    console.log('Longitude [Szerokość]: ' + userLocation.coordinates.longitude);
				console.log('Address: ' + userLocation.address);
				$('#search').attr('placeholder', 'Przygotowywanie wyników. Daj nam \'sekundkę\'.');
			   	$.getJSON('get_menus_for.php', {longitude: userLocation.coordinates.longitude, latitude: userLocation.coordinates.latitude}, drawCards).fail(function(){
			   		$('#error_box_text').text('Upss... wystąpił problem z wczytaniem wyników. Spróbuj ponownie lub samemu wpisz adres, dla którego chcesz znaleźć lokale, a jeżeli i to nie pomoże to poinformuj nas o tym problemie.');
			   		$('#error_box').fadeIn(800);
			   		$('#search').attr('placeholder', 'Niestety coś poszło nie tak :(.');
			   		$('#loading_icon').attr('src', 'resources/images/icons/svg/exclamation_mark.svg');
			   	});
			  	// Sending coordinates to server and waiting for restaurants list. DrawCards() take care of drawing restaurant cards.
			} else if(status === google.maps.GeocoderStatus.ERROR || status === google.maps.GeocoderStatus.INVALID_REQUEST || status === google.maps.GeocoderStatus.UNKNOWN_ERROR){
				console.log('NOTICE: GeocoderStatus != \'OK\' | GeocoderStatus = ' + status);
				$('#error_box_text').text('Nie udało się połączyć z serwerami Google. Spróbuj odświeżyć stronę lub wpisz adres samemu.');
				$('#error_box').fadeIn(800);
		   		$('#loading_icon').attr('src', 'resources/images/icons/svg/exclamation_mark.svg');
			} else if(status === google.maps.GeocoderStatus.ZERO_RESULTS){
				console.log('NOTICE: GeocoderStatus != \'OK\' | GeocoderStatus = ' + status);
				$('#error_box_text').text('Nie udało nam się rozszyfrować Twojej lokalizacji automatycznie. Wpisz adres, dla którego chcesz wyszukać lokale samemu.');
				$('#error_box').fadeIn(800);
		   		$('#loading_icon').attr('src', 'resources/images/icons/svg/exclamation_mark.svg');
			} else{
				console.log('ERROR: GeocoderStatus != \'OK\' | GeocoderStatus = ' + status);
				$('#error_box_text').text('Upss... Coś poszło nie tak. Prawdopodobnie kolejne próby również zakończą się niepowodzeniem :(. Daj nam znać o zaistniałym problemie.');
				$('#error_box').fadeIn(800);
		   		$('#loading_icon').attr('src', 'resources/images/icons/svg/exclamation_mark.svg');
			}
		})

	}

	function geolocationError(error) {
		console.log('ERROR: navigator.geolocation.getCurrentPosition() failed, reason: ' + error.message + '[code: ' + error.code + ']');
		$('#error_box_text').text('Nie udało się pobrać Twojej lokalizacji. Wpisz adres, dla którego chcesz wyszukać lokale samemu.');
		$('#error_box').fadeIn(800);
   		$('#loading_icon').attr('src', 'resources/images/icons/svg/exclamation_mark.svg');
	}


	$('#search').focus(function(){
		$('#loading_icon').removeAttr('src');
	});
    // Initializing jquery_ui window, which holds detailed information (map with directions, website, phone, menu) about restaurant.
    $('#dialog_place_details').dialog({
    	autoOpen: false,
    	show: 1500,
    	width: '80%',
    	height: 500,
    	position: {
    		my: 'center top',
    		at: 'center bottom',
    		of: '#search_box'
    	},
    	beforeClose: function() { //delete all inserted before information. If a new place doesn't have as many information, as previous one, This prevents repeat data from previous place.
    		$('#map_frame').empty();
    		$('#map_frame').removeAttr('src');
        	$('#dialog_place_name').empty();
        	$('#dialog_place_address').css('border-right-style', 'none');
        	$('#dialog_place_address_icon').empty();
        	$('#dialog_place_address_text').empty();
        	$('#dialog_place_phone').css('border-right-style', 'none');
    		$('#dialog_place_phone_icon').empty();
      		$('#dialog_place_phone_text').empty();
    		$('#dialog_place_website').css('border-right-style', 'none');
    		$('#dialog_place_website_icon').empty();
    		$('#dialog_place_website_text').empty();
    		$('#dialog_place_hours').css('border-right-style', 'none');
    		$('#dialog_place_hours_icon').empty();
    		$('#dialog_place_hours_text').empty();
        	$('#dialog_place_menu').empty();
        	$('#dialog_place_menu_description_text').empty();
        	$('#dialog_place_menu_price_icon').empty();
        	$('#dialog_place_menu_price_text').empty();
        	$('#dialog_place_menu_hours_icon').empty();
        	$('#dialog_place_menu_hours_text').empty();
    	}
    });
    $('#menu').keydown(function(){
    	var inputString = $(this).val();
    	var signsLeft = 255 - (inputString.length + 1);
    	if(signsLeft <= 0){
    		$('#menu').val(inputString.substr(0, inputString.length - 1));
    	}
    	$('#sign_counter_value').text(signsLeft);
    });
    $('#add_restaurant_href').click(function(event){
    	event.preventDefault();
    	$('#add_place_disclaimer').fadeIn(1000);
    });

    $('#close_cross_disclaimer').click(function(){
    	$('#add_place_disclaimer').fadeOut(800);
    });


    $(':input').focus(function(){
    	$(this).removeClass('input_error');
    	$(this).next('.error_description').remove();
    });

    $('#close_cross').click(function(){
    	$('#error_box').fadeOut(800);
    });

    $('#close_cross').mouseover(function(){
    	$(this).css( 'cursor', 'pointer' );
    });
    $('#close_cross_disclaimer').mouseover(function(){
    	$(this).css( 'cursor', 'pointer' );
    });

    $('#add_menu_form').submit(function(event){
    	event.preventDefault();
    	$('#submit_menu_button').prop('disabled', true).val('Wysyłanie menu...');
    	$.post('process_menu.php', $('#add_menu_form').serialize(), function(response){
	   		$('#submit_menu_button').prop('disabled', false).val('Wyślij');
	   		if(response.status == 'OK') {
	    		$dialogAMForm.dialog('close');
			   	$.getJSON('get_menus_for.php', {longitude: userLocation.coordinates.longitude, latitude: userLocation.coordinates.latitude}, drawCards).fail(function(){
			   		$('#error_box_text').text('Upss... wystąpił problem z wczytaniem wyników. Spróbuj ponownie lub samemu wpisz adres, dla którego chcesz znaleźć lokale, a jeżeli i to nie pomoże to poinformuj nas o tym problemie.');
			   		$('#error_box').fadeIn(800);
			   		$('#search').attr('placeholder', 'Niestety coś poszło nie tak :(.');
			   		$('#loading_icon').attr('src', 'resources/images/icons/svg/exclamation_mark.svg')})
	   		} else {
	   			$('#error_box_text').html('Wystąpił błąd przy dodawaniu Twojego menu.');
	   			$('#error_box').fadeIn(800);
	   			$.each(response.errors, function(name, value){
	   				$('#' + name).addClass('input_error');
	   				$('#' + name).after('<p class="error_description">' + value + '</p>');})
	   		}
    	}).fail(function(){
	   		$('#error_box_text').html('Upss... Nie udało się połączyć z serwerem i Twoje menu nie zostało dodane do bazy danych. Spróbuj ponownie, a jeżeli problem będzie dalej występować to poinformuj nas o tym problemie.');
	   		$('#error_box').fadeIn(800);
	   		$('#loading_icon').attr('src', 'resources/images/icons/svg/exclamation_mark.svg');
	   		$('#submit_menu_button').prop('disabled', false).val('Wyślij');
    	})
    });
    // Initializing jquery_ui window, which holds add new menu form.
    var $dialogAMForm = $('#dialog_add_menu_form');
    $dialogAMForm.dialog({
    	autoOpen: false,
    	show: 1500,
    	hide: 1500,
    	minHeight: 600,
    	minWidth: 740,
    	beforeClose: function() {
    		$('#menu').val('');
    		$('#price').val('');
    		$('#menu_name').val('');
    		$('#place_ID').val('');
    	}
    });
    $('#add_menu_form :input').button();
	var date = today;
	var time = '';
	if(date.getHours() > 16){
		date.setDate(today.getDate() + 1);
		time = '12:00';
	} else {
		var hours = date.getHours() < 10 ? '0' + date.getHours() : date.getHours();
		var minutes = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
		var time = '' + hours + ':' + minutes;
	}
	var month = date.getMonth() < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1;
	var day = date.getDate() < 10 ? '0' + (date.getDate()) : date.getDate();
    var dateISOShort = '' + date.getFullYear() + '-' + month + '-' + day;
	$('#menu_valid_from').attr('data-value', dateISOShort);
	$('#menu_valid_to').attr('data-value', dateISOShort);
	$('#valid_hours_start').val(time);
	$('#valid_hours_end').val('16:00');

    $('.date').pickadate({
		formatSubmit: 'yyyy-mm-dd',
		hiddenName: true,
		min: true,
		max: +30,
		container: '#body',
	});
	$('.hours').pickatime({
		clear: 'Wyczyść pole',
		container: '#body',
		format: 'HH:i',
		formatLabel: 'HH:i',
	});
    $('#content').on('click', '.add_menu_anchor', function(event) {
    	event.preventDefault(); //users without JS enabled uses server side scripting, we prevent this behavior in JS Enabled users.
    	var restaurantName = $(this).closest('.restaurant_card').find('.restaurant_name').text();
    	var placeID = $(this).attr('href').slice($(this).attr('href').indexOf('place_id=') + 'place_id='.length);
    	placeID = placeID.slice(0, placeID.indexOf('&'));
    	$('#restaurant_name').val(restaurantName);
    	$('#place_ID').val(placeID);
    	$dialogAMForm.dialog('open');
    	$dialogAMForm.dialog('option', 'title', 'Dodaj nowe menu dla lokalu - ' + restaurantName);
    })

    // Initializing jquery_ui forms enchantments.
    //$('#add_menu_form').button

    // this section prepare and opens jquery_ui window with detailed information about clicked place.
    $('#content').on('click', '.place_details_anchor', function(event) {
    	event.preventDefault(); //users without JS enabled uses server side scripting, we prevent this behavior in JS Enabled users.

    	var placeID = $(this).attr('href').slice($(this).attr('href').indexOf('=') + 1); //placeID contains Google Maps PlaceID extracted from url
    	var embedURL = 'https://www.google.com/maps/embed/v1/directions?'; //embedURL = base Google Maps Embed API Url
    	var apiKey = 'key=' + 'AIzaSyA5W3ND_ioyALTOGt4c1RPu2SeuH59IKzY'; //apiKey = Google API key
    	var origin = 'origin=' + encodeURIComponent(userLocation.address); // origin = encoded user address
    	var destination = 'destination=' + encodeURIComponent($(this).find('.restaurant_address').text()); //destination = encoded place address
    	var mode = 'mode=' + 'walking';
    	var url = embedURL + apiKey + '&' + origin + '&' + destination + '&' + mode;

    	console.log('EmbedURL: ', url);

    	$('#map_frame').attr('src', url);
    	$('#dialog_place_details').dialog('open');
    	$('#dialog_place_details').dialog('option', 'title', $(this).find('.restaurant_name').text() + ' | adres: ' + $(this).find('.restaurant_address').text());
    	$('#dialog_place_name').text($(this).find('.restaurant_name').text());
    	$('#dialog_place_address_text').text($(this).find('.restaurant_address').text());
    	$('#dialog_place_address_icon').html('<img src="resources/images/icons/svg/address.svg" alt="Adres" title="Adres" />');
    	$('#dialog_place_address').css('border-right', 'solid grey 1px');
    	$('#dialog_place_menu_hours_icon').html($(this).next('article').find('.valid_hour_icon').html());
    	$('#dialog_place_menu_hours_icon img').attr('src', 'resources/images/icons/svg/clock.svg');
    	$('#dialog_place_menu_hours_text').html($(this).next('article').find('.valid_hours_text').html());
    	$('#dialog_place_menu_description_icon').html('<img src="resources/images/icons/svg/menu.svg" alt="Menu" title="Menu" />');
    	$('#dialog_place_menu_description_text').html($(this).next('article').find('.restaurant_menu').html());
    	$('#dialog_place_menu_price_icon').html($(this).next('article').find('.menu_price_icon').html());
    	$('#dialog_place_menu_price_icon img').attr('src', 'resources/images/icons/svg/price_black.svg');
    	$('#dialog_place_menu_price_text').html($(this).next('article').find('.menu_price_text').html());
    	//
    	var yourPosition = new google.maps.LatLng(userLocation.coordinates.latitude, userLocation.coordinates.longitude);
    	map = new google.maps.Map(document.getElementById('map_frame'), {
    	    center: yourPosition,
    	    zoom: 17
    	  });
    	service = new google.maps.places.PlacesService(map);
    	service.getDetails({placeId: placeID}, function(place, status) {
    		if(place.formatted_phone_number !== undefined){
    			$('#dialog_place_phone_text').text(place.formatted_phone_number);
    			$('#dialog_place_phone_icon').html('<img src="resources/images/icons/svg/phone.svg" alt="Telefon" title="Telefon" />');
    			$('#dialog_place_phone').css('border-right', 'solid grey 1px');
    		}
    		if(place.website !== undefined){
    			$('#dialog_place_website_text').text(place.website);
    			var website = $('#dialog_place_website_text').text();
    			$('#dialog_place_website_text').wrapInner('<a href="' + website + '"></a>');
    			$('#dialog_place_website_icon').html('<img src="resources/images/icons/svg/website.svg" alt="Strona internetowa" title="Strona internetowa" />');
    			$('#dialog_place_website').css('border-right', 'solid grey 1px');
    		}
    		if(place.opening_hours !== undefined){
    			var days = [];
    			$.each(place.opening_hours.periods, function(name, value){
    				days[value.open.day] = '' + value.open.time.substr(0, 2) + ':' + value.open.time.substr(2, 2) + ' - ' + value.close.time.substr(0, 2) + ':' + value.close.time.substr(2, 2);
    			});
    			$('#dialog_place_hours_icon').html('<img src="resources/images/icons/svg/clock.svg" alt="Godziny otwarcia" title="Godziny otwarcia" />');
    			if(days[today.getDay()] !== undefined){
    				$('#dialog_place_hours_text').text(days[today.getDay()]);
    				var openHours = $('#dialog_place_hours_text').text();
    				var openText = 'Dziś - <span style="color:green">Otwarte (godz. ' + openHours + ')';
    				$('#dialog_place_hours_text').html(openText);
    			} else{
    				$('#dialog_place_hours_text').html('Dziś - <span style="color:red">Zamknięte</span>');
    			}
    			$('#dialog_place_hours_text').css('border-right-style', 'solid grey 1px');
    			var tooltipText = '<p>teraz: ';
    			tooltipText +=	place.opening_hours.open_now ? '<span style="color:green">Otwarte</span></p>' : '<span style="color:red">Zamknięte</span></p>';
    			$.each(place.opening_hours.weekday_text, function(name, value){
    				tooltipText += '<p>' + value + '</p>';
    			});
    			$('#dialog_place_hours').tooltip({
    				track: true,
    				content: tooltipText,
    				items: '#dialog_place_hours_text',
    			});
    		}
    	});
    })


   	function drawCards(ajax) {
    	if(ajax.status === 'ERROR'){
	   		$('#error_box_text').html(ajax['description']);
	   		$('#error_box').fadeIn(800);
	   		$('#search').attr('placeholder', 'Niestety coś poszło nie tak :(.');
	   		$('#loading_icon').attr('src', 'resources/images/icons/svg/exclamation_mark.svg');
	   		return false;
	   	}

		$('#search_box label').slideUp(1000);
		$('#search_box').animate({'width': '100%', 'top': '4em'}, 2000, function(){
			$(this).removeClass('beforeGPS');
			$(this).addClass('afterGPS');
			$('#loading_icon').removeAttr('src');
			$('#search').attr('placeholder', userLocation.address);
		});
		$('#search_plus_image').animate({'width': '100%', 'height': '1.5em', 'margin-top': '0.4em', 'margin-bottom': '0', 'border-radius': '0'}, 2000);
		$('#search_box').switchClass('beforeGPS', 'afterGPS', 1000, function() {
		   	$('#loading_icon').removeAttr('src');
		   	$('#search').attr('placeholder', userLocation.address);
		});

		var $oldCards = $('.restaurant_card');
    	//Draw places with todays menu
		if(ajax.todays_menus !== undefined){
			$('#zero_menus').remove();
		for(var i = 0; i < ajax.todays_menus.length; i++) {
       		var $restaurantCard = $('.restaurant_card:last').clone();
       		$restaurantCard.removeAttr('hidden');
       		var placeDetailsURL = 'place_details.php?place_id=' + ajax.todays_menus[i].place_id;
       		$restaurantCard.find('.place_details_anchor').attr('href', placeDetailsURL);
       		$restaurantCard.find('.restaurant_name').text(ajax.todays_menus[i].name);
       		$restaurantCard.find('.restaurant_address').html('<span class="street">' + ajax.todays_menus[i].vicinity.split(', ')[0] + '</span><span class="city">, ' + ajax.todays_menus[i].vicinity.split(', ').pop() + '</span>');
       		$restaurantCard.find('.restaurant_distance_to').text(ajax.todays_menus[i].distance + ' km');
       		$restaurantCard.find('.restaurant_menu').html(ajax.todays_menus[i].menu.description);
       		$restaurantCard.find('.menu_price_text').text(ajax.todays_menus[i].menu.price + ' zł');
       		$restaurantCard.find('.valid_hours_text').text(ajax.todays_menus[i].menu.valid_from_hour + ' - ' + ajax.todays_menus[i].menu.valid_to_hour);
       		$restaurantCard.find('.add_menu_anchor')
       					   .attr('href', 'add_menu.php?place_id=' + ajax.todays_menus[i].place_id + '&restaurant_name=' + encodeURIComponent(ajax.todays_menus[i].name));
       		$('#todays_menus').append($restaurantCard);
       		$('#todays_menus').delay(2000).fadeIn(2000);
       	}} else {
       		$('#todays_menus').delay(2000).fadeIn(2000);
       	}
       	if(ajax.tomorrows_menus !== undefined){
		for(var i = 0; i < ajax.tomorrows_menus.length; i++) {
       		var $restaurantCard = $('.restaurant_card:last').clone();
       		$restaurantCard.removeAttr('hidden');
       		var placeDetailsURL = 'place_details.php?place_id=' + ajax.tomorrows_menus[i].place_id;
       		$restaurantCard.find('.place_details_anchor').attr('href', placeDetailsURL);
       		$restaurantCard.find('.restaurant_name').text(ajax.tomorrows_menus[i].name);
       		$restaurantCard.find('.restaurant_address').html('<span class="street">' + ajax.tomorrows_menus[i].vicinity.split(', ')[0] + '</span><span class="city">, ' + ajax.tomorrows_menus[i].vicinity.split(', ').pop() + '</span>');
       		$restaurantCard.find('.restaurant_distance_to').text(ajax.tomorrows_menus[i].distance + ' km');
       		$restaurantCard.find('.restaurant_menu').html(ajax.tomorrows_menus[i].menu.description);
       		$restaurantCard.find('.menu_price_text').text(ajax.tomorrows_menus[i].menu.price + ' zł');
       		$restaurantCard.find('.valid_hours_text').text(ajax.tomorrows_menus[i].menu.valid_from_hour + ' - ' + ajax.tomorrows_menus[i].menu.valid_to_hour);
       		$restaurantCard.find('.add_menu_anchor')
       					   .attr('href', 'add_menu.php?place_id=' + ajax.tomorrows_menus[i].place_id + '&restaurant_name=' + encodeURIComponent(ajax.tomorrows_menus[i].name));
       		$('#tomorrows_menus').append($restaurantCard);
       		$('#tomorrows_menus').delay(2000).fadeIn(2000);
       	}}
       	if(ajax.no_menu !== undefined){
       		$('.restaurant_card:last .menu_price_icon').empty();
       		$('.restaurant_card:last .valid_hour_icon').empty();
		for(var i = 0; i < ajax.no_menu.length; i++) {
       		var $restaurantCard = $('.restaurant_card:last').clone();
       		$restaurantCard.removeAttr('hidden');
       		var placeDetailsURL = 'place_details.php?place_id=' + ajax.no_menu[i].place_id;
       		$restaurantCard.find('.place_details_anchor').attr('href', placeDetailsURL);
       		$restaurantCard.find('.restaurant_name').text(ajax.no_menu[i].name);
       		$restaurantCard.find('.restaurant_address').html('<span class="street">' + ajax.no_menu[i].vicinity.split(', ')[0] + '</span><span class="city">, ' + ajax.no_menu[i].vicinity.split(', ').pop() + '</span>');
       		$restaurantCard.find('.restaurant_distance_to').text(ajax.no_menu[i].distance + ' km');
       		$restaurantCard.find('.restaurant_menu').html('Brak menu na dzisiejszy dzień.');
       		$restaurantCard.find('.add_menu_anchor')
       					   .attr('href', 'add_menu.php?place_id=' + ajax.no_menu[i].place_id + '&restaurant_name=' + encodeURI(ajax.no_menu[i].name));
       		$('#no_menu').append($restaurantCard);
       		$('#no_menu').delay(2000).fadeIn(2000);
       	}}
		//$('.restaurant_card:last').remove();
		$oldCards.remove();
   	}
})