<?php

/**
 * @version $Id: StripTagAttributes.php v1.0.0 2024-04-23 12:00:00 ks $
 * @package forms
 *
 */

namespace App\Services\Requests\Filters;

trait StripTagAttributesFilter{

    /**
     * strips not allowed attributes
     *
     * @param string $string
     * @return string
     */
    protected function filter_StripTagAttributes($string)
    {
        return preg_replace_callback('/<(.*?)>/i',[
            $this,
            'filter_StripAttributes'
        ], $string);
    }

    private function filter_StripAttributes($string)
    {
        if (empty($string[0])) {

            return '';
        }

        $disabled_attributes = [
            'onabort',
            'onactivate',
            'onafterprint',
            'onafterupdate',
            'onbeforeactivate',
            'onbeforecopy',
            'onbeforecut',
            'onbeforedeactivate',
            'onbeforeeditfocus',
            'onbeforepaste',
            'onbeforeprint',
            'onbeforeunload',
            'onbeforeupdate',
            'onblur',
            'onbounce',
            'oncellchange',
            'onchange',
            'onclick',
            'oncontextmenu',
            'oncontrolselect',
            'oncopy',
            'oncut',
            'ondataavaible',
            'ondatasetchanged',
            'ondatasetcomplete',
            'ondblclick',
            'ondeactivate',
            'ondrag',
            'ondragdrop',
            'ondragend',
            'ondragenter',
            'ondragleave',
            'ondragover',
            'ondragstart',
            'ondrop',
            'onerror',
            'onerrorupdate',
            'onfilterupdate',
            'onfinish',
            'onfocus',
            'onfocusin',
            'onfocusout',
            'onhelp',
            'onkeydown',
            'onkeypress',
            'onkeyup',
            'onlayoutcomplete',
            'onload',
            'onlosecapture',
            'onmousedown',
            'onmouseenter',
            'onmouseleave',
            'onmousemove',
            'onmoveout',
            'onmouseover',
            'onmouseup',
            'onmousewheel',
            'onmove',
            'onmoveend',
            'onmovestart',
            'onpaste',
            'onpropertychange',
            'onreadystatechange',
            'onreset',
            'onresize',
            'onresizeend',
            'onresizestart',
            'onrowexit',
            'onrowsdelete',
            'onrowsinserted',
            'onscroll',
            'onselect',
            'onselectionchange',
            'onselectstart',
            'onstart',
            'onstop',
            'onsubmit',
            'onunload'
        ];

        return preg_replace(array(
            '/javascript:[^\"\']*/i',
            '/(' . implode('|', $disabled_attributes) . ')[\s]*=([\"\']?)(?(?<![\'\"])[^\s\>\/]*|\1)/mi',
            '/\s+/'
        ), array(
            '',
            '',
            ' '
        ), $string[0]);
    }

}
