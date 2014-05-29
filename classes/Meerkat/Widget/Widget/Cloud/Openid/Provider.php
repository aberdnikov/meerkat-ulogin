<?php

namespace Meerkat\Widget;

class Widget_Cloud_Openid_Provider extends Widget {

    /**
     * @return Widget_OpenidAccounts
     */
    static function instance() {
        return self::_instance();
    }

    static function to_html($params = array()) {
        $id = \Meerkat\User\Me::id();
        if (!$id) {
            return '';
        }
        $items = \ORM::factory('Ulogin')->where('user_id', '=', $id)->find_all()->as_array();
        $tpl = self::get_template();
        $tpl->set('items', $items);
        return $tpl->render();
    }

}