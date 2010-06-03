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
 * Smarty function to get a country name from a given country name.
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - code:     Country code to get the corresponding name for
 *
 * Example
 *   {get_country_name_for_country_code  code=ZZ}
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param object &$smarty Reference to the Smarty object.
 *
 * @return      string      the value of the last status message posted, or void if no status message exists
 */
function smarty_function_get_country_name_for_country_code ($params, &$smarty)
{
    $code   = strtolower(isset($params['code']) ? $params['code'] : 'ZZ');
    $assign = isset($params['assign']) ? $params['assign'] : null;

    $countries = ZLanguage::countryMap();
    if (isset($countries[$code])) {
        $result = $countries[$code];
    } else {
        $result = $countries['ZZ'];
    }

    if ($assign) {
        $smarty->assign ($assign, $result);
    } else {
        return $result;
    }
}
