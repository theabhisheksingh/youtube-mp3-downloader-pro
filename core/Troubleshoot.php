<?php

namespace uniqueX;

class Troubleshoot
{
    public static function safeMode()
    {
        return function_exists('ini_get') && ini_get('safe_mode');
    }

    public static function disabledFunctions($functions)
    {
        $return = array();

        // Get disabled functions
        $disabledFunctions = array();
        if (function_exists('ini_get') && is_string($dFn = ini_get('disable_functions'))) {
            $disabledFunctions = explode(',', preg_replace('/\s+/i', '', strtolower($dFn)));
        }

        // Check functions are disabled or not
        if (count($disabledFunctions) > 0 && is_array($functions) && count($functions) > 0) {
            foreach ($functions as $fn) {
                $return[$fn] = in_array(trim($fn), $disabledFunctions);
            }
        }

        // Dump final result
        return $return;
    }

    public static function serverOS()
    {
        $return = false;
        // Get server uname
        $uName = function_exists('php_uname') ? php_uname() : '';
        $uName = is_string($uName) ? $uName : '';
        // Check for linux
        if (stripos($uName, 'linux') !== false) {
            $return = 'linux';
        }
        // Check for windows
        if (is_bool($return) && stripos($uName, 'windows') !== false) {
            $return = 'win';
        }
        // Dump OS name
        return $return;
    }

    public static function serverType()
    {
        return strpos(php_uname('m'), '64') !== false ? 64 : 32;
    }

    public static function execCMD($command)
    {
        $return = null;
        if (is_string($command) &&
            function_exists('proc_open') &&
            function_exists('proc_close') &&
            is_resource($proc = @proc_open($command, array(1 => array("pipe", "w")), $pipes))
        ) {
            if (is_string($dump = stream_get_contents($pipes[1]))) {
                $return = $dump;
            }
            fclose($pipes[1]);
            proc_close($proc);
        }
        return $return;
    }

    public static function printDetails($title, $req)
    {
        ?>
        <li data-preq="php_version">
                    <span class="preq-status status-<?php
                    echo isset($req['status']) && $req['status'] ? 'ok' : 'no';
                    ?>">
                        <span class="fa fa-check-square"></span>
                        <span class="fa fa-square"></span>
                    </span>
            <span class="preq-name"><?php echo $title; ?></span>
            <?php
            if (isset($req['notes']) && is_array($req['notes']) && count($req['notes']) > 0) {
                echo '<br><ul class="notes">';
                foreach ($req['notes'] as $note) {
                    echo "<li>{$note}</li>";
                }
                echo '</ul>';
            }
            ?>
        </li>
        <?php
    }
}
