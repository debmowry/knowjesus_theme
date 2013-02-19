<?php

$THEME->sheets = array('css/style');

$THEME->standardsheets = array('styles_layout','styles_fonts','styles_color','styles_moz');  

$THEME->parent = '';  

$THEME->parentsheets = false;  

$THEME->modsheets = true;  

$THEME->blocksheets = true;  

$THEME->langsheets = false;  

$THEME->navmenuwidth = 50;

$THEME->makenavmenulist = false;  

$THEME->resource_mp3player_colors = 
 'bgColour=000000&btnColour=ffffff&btnBorderColour=cccccc&iconColour=000000&'.
 'iconOverColour=00cc00&trackColour=cccccc&handleColour=ffffff&loaderColour=ffffff&'.
 'font=Arial&fontColour=3333FF&buffer=10&waitForPlay=no&autoPlay=yes';

/// With this you can control the colours of the "big" MP3 player 
/// that is used for MP3 resources.

$THEME->filter_mediaplugin_colors = 
 'bgColour=000000&btnColour=ffffff&btnBorderColour=cccccc&iconColour=000000&'.
 'iconOverColour=00cc00&trackColour=cccccc&handleColour=ffffff&loaderColour=ffffff&'.
 'waitForPlay=yes';

/// ...And this controls the small embedded player

$THEME->custompix = true;

/// If true, then this theme must have a "pix" 
/// subdirectory that contains copies of all 
/// files from the moodle/pix directory, plus a
/// "pix/mod" directory containing all the icons 
/// for all the activity modules.

?>
