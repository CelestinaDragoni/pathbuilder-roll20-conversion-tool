<?php

    chdir(dirname(__FILE__));
    set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__));

    function errorHandler() {
        throw new Exception('PHP Fatal Error');
    }
    set_error_handler("errorHandler");

    require_once('models/player.model.php');
    require_once('services/conversion.service.php');
    require_once('services/document.service.php');
    require_once('factory/player.factory.php');

    if (php_sapi_name() === 'cli'):
        require_once('tools/cli.tool.php');
    endif;

