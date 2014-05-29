<?php

class Controller_Admin_Ulogin extends Controller_Admin {

    function before() {
        parent::before();
        Meerkat\Widget\Widget_Breadcrumbs::instance()->add('OpenID', Kohana::$config->load('meerkat/ulogin.url.public_ulogin'));
    }

    function action_index() {
        //Meerkat\Base\Page_Layout::instance()->template(true);
        //Auth::instance()->logout(true,true);
    }

    function action_accounts() {
        Meerkat\Widget\Widget_Breadcrumbs::instance()->add('Аккаунты', Kohana::$config->load('meerkat/ulogin.url.admin_ulogin_accounts'));
        //Meerkat\Base\Page_Layout::instance()->template(true);
        //Auth::instance()->logout(true,true);
    }

}