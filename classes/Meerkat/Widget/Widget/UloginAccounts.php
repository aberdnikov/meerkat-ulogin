<?php

namespace Meerkat\Widget;

class Widget_UloginAccounts extends Widget {

    /**
     * @return Widget_OpenidAccounts
     */
    static function instance() {
        return self::_instance();
    }

    static function to_html($params = array()) {
        \Meerkat\StaticFiles\Js::instance()->add_onload('
            $("[data-action=ulogin_free]").click(function(){
                $.getScript("'.\Kohana::$config->load('meerkat/ulogin.url.ajax_ulogin_free').'?id="+$(this).attr("data-id"));
            });
        ');
        $id = \Meerkat\User\Me::id();
        if (!$id) {
            return 'нет подключенных аккаунтов';
        }
        $items = \ORM::factory('Ulogin')->where('user_id', '=', $id)->find_all()->as_array();
        $tpl = self::get_template();
        $tpl->set('items', $items);
        return $tpl->render();
    }

}