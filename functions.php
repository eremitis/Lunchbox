<?php

//SPL class autoload function
function class_autoloader($class)
{
    require_once 'classes/' . $class . '.php';
}

/*
 * Funkcja wyliczajaca odleglosc pomiedzy dwoma miejscowosciami na podstawie wspolrzednych geograficznych.
 * Copyright (C) 2012 www.bazamiejscowosci.pl

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


function computeDistance( $locality1_latitude, $locality1_longitude, $locality2_latitude, $locality2_longitude ) {

    $tile1 = sin( deg2rad( $locality1_latitude ) ) * sin( deg2rad( $locality2_latitude ) );
    $tile2 = cos( deg2rad( $locality1_latitude ) ) * cos( deg2rad( $locality2_latitude ) );
    $tile3 = cos( deg2rad( $locality2_longitude - $locality1_longitude ) );
    $result = $tile1 + $tile2 * $tile3;
    $result = acos( $result ) * 6371;
    return round($result, 1);
}

function sort_by_distance($left, $right) {
    if ($left['distance'] == $right['distance'])
        return 0;
    if ($left['distance'] > $right['distance'])
        return 1;
    else
        return 0;
}
?>