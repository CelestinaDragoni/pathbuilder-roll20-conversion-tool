<?php

/**
 * Handles I/O for PHP Command Line Scripts
 */
class CLI {

    static $color = false;
    static $colorTerminals = [
        'xterm-color',
        'xterm-16color',
        'xterm-256color'
    ];

    static function checkColorConsoleSupport() {
        $terminal = trim(shell_exec('echo $TERM'));
        if (in_array($terminal, Self::$colorTerminals)) {
            Self::$color = true;
        }
    }

    /**
     * Handle user input
     * @param String $prompt - Line of text you wish to display.
     * @param Function $callback - Validation callback for response.
     * @param String $invalid - Response when validation callback returns false.
     * @return Void
     */
    static function input($prompt, $callback=null, $invalid="Invalid Response")
    {
        while (true) {
            echo "{$prompt}: ";
            $buffer = strtolower(trim(fgets(STDIN)));
            if (is_callable($callback)) {
                if($callback($buffer)) {
                    return $buffer;
                } else {
                    echo "\n{$invalid}\n";
                }
            } else {
                return $buffer;
            }
        }
    }

    /**
     * Draws a decorative box.
     * @param String $prompt - Line of text you wish to display.
     * @return Void
     */
    static function box($prompt)
    {

        $drawLine = function($width) {
            for ($i=0;$i<$width;$i++) {
                if ($i == 0 || $i+1 == $width)
                    echo '+';
                else
                    echo '-';
            }
            echo "\n";
        };

        $boxWidth = strlen($prompt)+4;
        $drawLine($boxWidth);
        echo "| {$prompt} |\n";
        $drawLine($boxWidth);

    }

    /**
     * Draws a standard header.
     * @param String $prompt - Line of text you wish to display.
     * @return Void
     */
    static function header($prompt)
    {
        echo "\n{$prompt}\n";
        for ($i=0;$i<strlen($prompt);$i++) {
            echo '-';
        }
        echo "\n\n";
    }

    /**
     * Dump Variable Output
     * @param Mixed $buffer - Data you wish to dump to the console.
     * @return Void
     */
    static function dump($buffer)
    {
        ob_start();
            var_dump($buffer);
        $buffer = ob_get_contents();
        ob_end_clean();

        echo "--|DUMP|--------------\n";
        echo $buffer;
        echo "\n----------------------\n";
    }

    /**
     * Success message
     * @param String $prompt - Line of text you wish to display.
     * @return Void
     */
    static function success($prompt)
    {
        echo Self::$color ? "\033[1;32m[*]\033[0m   {$prompt}\n" : "[*]   {$prompt}\n";
    }

    /**
     * Warn message
     * @param String $prompt - Line of text you wish to display.
     * @return Void
     */
    static function warn($prompt)
    {
        echo Self::$color ? "\033[1;33m[¿]\033[0m   {$prompt}\n" : "[¿]   {$prompt}\n";
    }

    /**
     * Info message
     * @param String $prompt - Line of text you wish to display.
     * @return Void
     */
    static function info($prompt)
    {
        echo Self::$color ? "\033[1;36m[?]\033[0m   {$prompt}\n" : "[?]   {$prompt}\n";
    }

    /**
     * Error message
     * @param String $prompt - Line of text you wish to display.
     * @return Void
     */
    static function error($prompt)
    {
        echo Self::$color ? "\033[1;31m[!]\033[0m   {$prompt}\n" : "[!]   {$prompt}\n";
    }

    /**
     * Fatal message, exit on display (if exit=2)
     * @param String $prompt - Line of text you wish to display.
     * @param Bool $exit - Exit on fatal? Default is true.
     * @param Integer $return - Return value back to shell. Default is -1
     * @return Void
     */
    static function fatal($prompt, $exit=true, $return=-1)
    {
        echo Self::$color ? "\033[1;31m[!!]\033[0m  {$prompt}\n" : "[!!]  {$prompt}\n";
        if ($exit) {
            exit($return);
        }
    }

}
