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
 * Smarty modifier to convert lanugage define into textual string
 *
 * This modifier converts a lanugage define (currently a defined contant e.g.
 * _MYCONST) into the language string represented by that define
 *
 *
 * Example
 *
 *   {$MyVar|ml}
 *
 * @see          modifier.varprepfordisplay.php::smarty_modifier_varprepfordisplay()
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_ml($string)
{
    return pnML($string);
}
