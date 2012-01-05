<?php
/*
 * Copyright (c) 2010 Litotex
 * 
 * Permission is hereby granted, free of charge,
 * to any person obtaining a copy of this software and
 * associated documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy,
 * modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit
 * persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * 
 * The above copyright notice and this permission notice
 * shall be included in all copies or substantial portions
 * of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */
/**
 * @package acp_navigation
 * @author Patrick K�nig <koenig@mail-buero.de>
 * @copyright: Copyright 2011 Litotex Team
 */
class package_acp_navigation extends acpPackage {
    /**
     * Package name
     * @var string
     */
    protected $_packageName = 'acp_navigation';
    /**
     * Avaibilbe actions in this package
     * @var array
     */
    protected $_availableActions = array();
    
    protected $_theme = 'empty.tpl';
    
    public function __action_main() {
        return true;
    }
    public static function registerTplModifications(){
        self::_registerTplModification(__CLASS__, 'displayAcpTopNavigation', 'acp_navigation');
        self::_registerTplModification(__CLASS__, 'displayAcpSubNavigation', 'acp_navigation');
        return true;
    }
    public static function __tpl_displayAcpTopNavigation(){
        package::addJsFile('main.js','acp_navigation');
        //package::addJsFile('jquery.effects.core.min.js');
        $elements = array();
        $data = self::$db->Execute("SELECT `ID`, `title`, `description`, `icon`, `package`, `action` FROM `lttx_acp_navigation` WHERE `parent` IS NULL ORDER BY `sort` ASC");
        while(!$data->EOF) {
            if(!isset($_GET['package'])) $_GET['package'] = 'main';
            $res = package::$db->Execute("SELECT COUNT(*) FROM `lttx_acp_navigation` WHERE `parent` = ? AND `package` = ?", array($data->fields['ID'], $_GET['package']));
            if($res->fields[0] >= 1)
                $data->fields['active'] = true;
            else
                $data->fields['active'] = false;
            $elements[] = $data->fields;
            $data->MoveNext();
        }
        self::$tpl->assign('navigationItems', $elements);
        self::$tpl->display(self::getTplDir('acp_navigation') . 'topNavigation.tpl');
    }
    public static function __tpl_displayAcpSubNavigation(){
        $counter=0;
        $data = self::$db->Execute("SELECT `ID`, `parent`, `title`, `description`, `icon`, `package`, `action`, `tab` FROM `lttx_acp_navigation` WHERE `parent` IS NOT NULL ORDER BY `sort` ASC");
        $elements = array();
        while(!$data->EOF) {
            $counter++;
            $real = self::$db->Execute("SELECT COUNT(*) FROM `lttx_acp_navigation` WHERE `parent` IS NULL AND `ID` = ?", array($data->fields['parent']));
            if($real->fields[0] < 1) {
                $data->MoveNext();
                continue;
            }
            $sub = self::$db->Execute("SELECT `ID`, `parent`, `title`, `description`, `icon`, `package`, `action`, `tab` FROM `lttx_acp_navigation` WHERE `parent` = ? ORDER BY `sort` ASC", array($data->fields['ID']));
            if(!isset($elements[$data->fields['parent']]))
                $elements[$data->fields['parent']] = array('active' => false);
                $subElements = array();
                $active = false;
                
            if(!isset($_GET['package']))
                $_GET['package'] = 'main';
            if($data->fields['package'] == $_GET['package']){
                $active = true;
            }
            while(!$sub->EOF) {
                $subElements[] = $sub->fields;
                if(!isset($_GET['package']))
                    $_GET['package'] = 'main';
                if($sub->fields['package'] == $_GET['package']) {
                    $active = true;
                }
                $sub->MoveNext();
            }
            $data->fields['sub'] = $subElements;
            $elements[$data->fields['parent']][] = $data->fields;
            if($active==true)
                $elements[$data->fields['parent']]['active'] = true;
                $data->MoveNext();
        }
        self::$tpl->assign('navigationItems', $elements);
        self::$tpl->display(self::getTplDir('acp_navigation') . 'subNavigation.tpl');
    }
}
?>