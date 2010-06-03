<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to get the site's page render time
 *
 * available parameters:
 *  - assign      if set, the message will be assigned to this variable
 *  - round       if the, the time will be rounded to this number of decimal places
 *                (optional: default 2)
 *
 * Example
 * {pagerendertime} outputs 'Page created in 0.18122792243958 seconds.'
 *
 * {pagerendertime round=2} outputs 'Page created in 0.18 seconds.'
 *
 * @param    array    $params     All attributes passed to this function from the template
 * @param    object   $smarty     Reference to the Smarty object
 * @param    string   $round      format to apply to the number (based on the round php function)
 * @return   string   the page render time in seconds
 */
function smarty_function_pagerendertime($params, &$smarty)
{
    // show time to render
    if ($GLOBALS['ZConfig']['Debug']['pagerendertime']){
        // calcultate time to render
        $mtime = explode(' ',microtime());
        $dbg_endtime = $mtime[1] + $mtime[0];
        $dbg_totaltime = ($dbg_endtime - $GLOBALS['ZRuntime']['dbg_starttime']);

        $round = isset($params['round']) ? $params['round'] : 2;
        $dbg_totaltime = round($dbg_totaltime, $round);

        if (isset($params['assign'])) {
            $smarty->assign('rendertime', $dbg_totaltime);
        } else {
            // load language files
            $message = '<div class="z-sub" style="text-align:center;">' . __f('Page generated in %s seconds.', $dbg_totaltime) . '</div>';
            return $message;
        }
    }
}
