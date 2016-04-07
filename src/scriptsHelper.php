<?php
/**
 * Created by PhpStorm.
 * User: philippe
 * Date: 05/04/16
 * Time: 14:14
 */

use \Wiidoo\ScriptsHelper\Lib\Scripts;

function scriptHelper()
{
    global $SCRIPTSHELPERCLASS;

    if (!$SCRIPTSHELPERCLASS) {
        $SCRIPTSHELPERCLASS = new Scripts();
    }

    return $SCRIPTSHELPERCLASS;
}