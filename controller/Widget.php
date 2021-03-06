<?php
/**
 * Ads board module for Cotonti Siena
 *
 * @package Advboard
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright (c) 2015-2016 Portal30 Studio http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL.');

if(empty(cot::$R['list_more']) && cot_module_active('page')) require_once cot_incfile('page', 'module', 'resources');

class advboard_controller_Widget
{
    /**
     * Widget to display ads list or single ad
     *
     * @param array     $condition
     * @param string    $tpl
     * @param int       $items
     * @param string    $order
     * @param bool      $onlyActive
     * @param string    $pagination
     * @param array     $params
     * @return string
     */
    public static function widget($condition = array(), $tpl = 'advboard.widget.list', $items = 0, $order = '',
                                   $onlyActive = true, $pagination = 'pld', $params = array())
    {
        // Get pagination number if necessary
        if (!empty($pagination)) {
            list($pg, $d, $durl) = cot_import_pagenav($pagination, $items);

        } else {
            $d = 0;
        }

        if(empty($condition)) $condition = array();

        if($onlyActive) {
            $condition[] = array('begin', cot::$sys['now'], '<=');
            $condition[] = array('SQL', "expire = 0 OR expire > ".cot::$sys['now']);
            $condition[] = array('state', advboard_model_Advert::PUBLISHED);
        }

        if(empty($order)) {
            $order = array(
                array('state', 'ASC'),
                array('sort', 'DESC'),
            );
        }

        /* === Hook === */
        foreach (cot_getextplugins('advboard.widget.list.query') as $pl) {
            include $pl;
        }
        /* ===== */

        $advertisement = NULL;
        $totallines = advboard_model_Advert::count($condition);
        if($totallines > 0) $advertisement = advboard_model_Advert::findByCondition($condition, $items, $d, $order);

        // Render pagination
        if(empty($params['module'])) {
            $params['module'] = defined('COT_PLUG') ? 'plug' : cot::$env['ext'];
        }

        if(empty($params['urlParams'])) {
            if (defined('COT_LIST')) {
                global $list_url_path;
                $params['urlParams'] = $list_url_path;

            } elseif (defined('COT_PAGES')) {
                global $al, $id, $pag;
                $params['urlParams'] = empty($al) ? array('c' => $pag['page_cat'], 'id' => $id) : array('c' => $pag['page_cat'], 'al' => $al);

            } else {
                $params['urlParams'] = array();
            }
        }

        if(empty($params['ajax'])) $params['ajax'] = false;
        if(empty($params['target_div'])) $params['target_div'] = '';
        if(empty($params['ajax_module'])) $params['ajax_module'] = '';
        if(empty($params['ajax_params'])) $params['ajax_params'] = array();

        $pagenav = cot_pagenav($params['module'], $params['urlParams'], $d, $totallines, $items, $pagination, '', $params['ajax'],
            $params['target_div'], $params['ajax_module'], $params['ajax_params']);

        if(empty($pagenav['current'])) $pagenav['current'] = 1;


        $view = new View();

        /* === Hook === */
        foreach (cot_getextplugins('advboard.widget.list.view') as $pl) {
            include $pl;
        }
        /* ===== */

        $view->advertisement = $advertisement;
        $view->totalItems = $totallines;
        $view->pagenav = $pagenav;

        return $view->render($tpl);
    }

    /**
     * @deprecated use static::widget()
     */
    public static function adsList($condition = array(), $tpl = 'advboard.widget.list', $items = 0, $order = '',
        $onlyActive = true, $pagination = 'pld', $params = array()) {

        return static::widget($condition, $tpl, $items, $order, $onlyActive, $pagination, $params);
    }

    /**
     * Ads compare widget
     * @param string $tpl
     * @return string
     */
    public static function compare($tpl = 'advboard.widget.compare') {

        static $exists = null;

        if(!empty($_SESSION['advboard_compare']) && $_SESSION['advboard_compare'][cot::$sys['site_id']]) {
            $ids = array_keys($_SESSION['advboard_compare'][cot::$sys['site_id']]);

            $ids[] = 8;
            $ids[] = 18;

            if(!empty($ids)) {
                if($exists === null) {
                    $exists = cot::$db->query("SELECT id FROM " . advboard_model_Advert::tableName() . " WHERE id IN (" .
                        implode(',', $ids) . ")")->fetchAll(PDO::FETCH_COLUMN);

                    if(empty($exists)) $exists = false;
                }

                if(empty($exists)) {
                    unset($_SESSION['advboard_compare']);

                } else {
                    $del = array_diff($ids, $exists);
                    if(!empty($del)) {
                        foreach ($del as $row) {
                            unset($_SESSION['advboard_compare'][cot::$sys['site_id']][$row]);
                        }
                    }
                }
            }
        }

        $totallines = 0;
        if(!empty($_SESSION['advboard_compare'])) $totallines = count($_SESSION['advboard_compare'][cot::$sys['site_id']]);

        if(empty($tpl)) $tpl = array('advboard', 'widget', 'compare');

        $view = new View();
        $view->advertisement = $_SESSION['advboard_compare'][cot::$sys['site_id']];
        $view->totalItems = $totallines;

        return $view->render($tpl);
    }
}