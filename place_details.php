<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta charset="UTF-8" />
		<title>Lunchbox</title>
		<link href="resources/images/icons/favicon.png" type="image/png" rel="shortcut icon" />
		<link href="resources/css/styles.css" rel="stylesheet" />
        <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
	</head>
	<body id="body">
 		<header id="main_header">
 			<a href="/lunchbox/">
 				<div id="site_name_plus_logo">
 					<h1 id="site_name">LunchBox</h1><div id="logo"></div>
 				</div>
 			</a>
            <nav>
            	<ul id="header_navigation">
                	<a href="account_settings.php" title="Ustawienia użytkownika" hidden="hidden">
                		<li id="account_settings">Ustawienia użytkownika</li>
               		</a>
               		<a href="add_restaurant.php" title="Dodaj nową restaurację" id="add_restaurant_href">
               			<li id="add_restaurant">Dodaj nową restaurację</li>
					</a>
               	</ul>
            </nav>
		</header> <!-- header end  -->
        <div id="content">
            <div id="search_box" class="afterGPS">
        		<form method="get" action="get_menus_for.php">
        			<label id="initial_status_box" for="search">Znajdź lokale w pobliżu adresu:<br /></label>
        			<div id="search_plus_image">
        				<img id="loading_icon" />
        				<input id="search" type="search" name="address" placeholder="np. Włodarzewska 87, Warszawa" autofocus="autofocus" />
        				<input id="search_icon" type="image" src="resources/images/icons/search.svg" />
        			</div>
        		</form>
        	</div>
        	<div id="add_place_disclaimer">
                <p>Przykro nam ale na chwilę obecną nasza strona wymaga technologii JavaScript. Aby móc korzystać z naszego serwisu
                    w pełni włącz JavaScript lub przełącz się na przeglądarkę, która tę technologię obsługuje.</p>
            </div>
        </div><!-- content end -->
	</body>
</html>