<?php

    $json = file_get_contents('pathbuilder-export.json');
    require_once('classes/player.class.php');
    require_once('services/conversion.service.php');

    $player = new PathbuilderPlayer($json);
    $struct = Roll20ConversionService::getInstance()->convert($player);

    echo json_encode($struct, JSON_PRETTY_PRINT);

