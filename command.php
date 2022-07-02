<?php

    chdir(dirname(__FILE__));

    if (php_sapi_name() !== 'cli') {
        exit();
    }

    require_once('bootstrap.php');

    CLI::checkColorConsoleSupport();

    function renderHelp()
    {
        echo "\nPathbuilder 2e to Roll 20 VTT Command Line Tool\n";
        echo "Usage: php command.php --input=file.json > roll20.json \n\n";
        exit();
    }

    $options = getopt('i:o:h', [
        "input:",
        "help",
    ]);

    if (empty($options) || isset($options['help']) || !isset($options['input'])) {
        renderHelp();
    }

    if (!file_exists($options['input'])) {
        CLI::fatal('Input File Doesn\'t Exist.');
    }

    $player = null;
    $json = file_get_contents($options['input']);
    try {
        $player = \PBR20\Factory\Player::create($json);
    } catch (Exception $e) {
        CLI::fatal('Invalid JSON Struct.');
    }

    $struct = \PBR20\Services\Conversion::getInstance()->convert($player);

    echo json_encode($struct, JSON_PRETTY_PRINT);
