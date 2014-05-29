<?php

namespace Meerkat\Widget;

class Widget_Ulogin extends Widget {

    /**
     * @return Widget_Ulogin
     */
    static function instance() {
        return self::_instance();
    }

    static function to_html($params = array()) {
        return \Ulogin::factory()->render();
    }

}