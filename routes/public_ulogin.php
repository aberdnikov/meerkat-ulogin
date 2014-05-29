<?php

    use \Meerkat\Widget\Widget_NavList;
    use \Meerkat\Widget\Widget_Navbar_Top;

    Route::set('ajax_ulogin_free', trim(Kohana::$config->load('meerkat/ulogin.url.ajax_ulogin_free'), '/'))
        ->defaults(
            array(
                'directory'  => 'Ajax',
                'controller' => 'Ulogin',
                'action'     => 'free',
            )
        );
    Route::set('public_ulogin', trim(Kohana::$config->load('meerkat/ulogin.url.public_ulogin'), '/'))
        ->defaults(
            array(
                'directory'  => 'Public',
                'controller' => 'Ulogin',
                'action'     => 'index',
            )
        );
    Route::set('public_ulogin_register', trim(Kohana::$config->load('meerkat/ulogin.url.public_ulogin_register'), '/'))
        ->defaults(
            array(
                'directory'  => 'Public',
                'controller' => 'Ulogin_Register',
                'action'     => 'index',
            )
        );
    \Meerkat\Event\Event::dispatcher()
        ->connect('Controller_Public_Register::form_build', function (sfEvent $event) {
            $data = Session::instance()
                ->get('ulogin');
            $form = $event->getSubject();
            $form->init_values(array(
                'login'    => \Meerkat\Helper\Helper_Text::slug(Arr::get($data, 'username')),
                'username' => Arr::get($data, 'username'),
                'email'    => Arr::get($data, 'email'),
            ));
            \Meerkat\Core\Page_TplVar::instance()
                ->set('openid', $data);

        });
    \Meerkat\Event\Event::dispatcher()
        ->connect('Controller_Public_Register::done', function (sfEvent $event) {
            $params        = $event->getParameters();
            $user          = Arr::get($params, 'user');
            $ulogin_id = Session::instance()
                ->get('ulogin_id');
            //привяжем OpenID-аккаунт к текущему аккаунту
            $ulogin = ORM::factory('Ulogin', $ulogin_id);
            if($ulogin->loaded()){
                $ulogin->user_id = $user->pk();
                $ulogin->save();
            }
            //удалим данные в сессии
            Session::instance()
                ->delete('ulogin_id');
            Session::instance()
                ->delete('ulogin');
        });
    if (\Meerkat\User\Me::id()) {
        Widget_Navbar_Top::instance()
            ->map_right()
            ->add('user.ulogin', \Meerkat\Html\Icon_Famfamfam::icon(\Meerkat\Html\Icon_Famfamfam::_KEY) . ' OpenID ', Kohana::$config->load('meerkat/ulogin.url.public_ulogin'));
    }
    \Meerkat\Event\Event::dispatcher()
        ->connect('Auth_Meerkat::complete_login', function (sfEvent $event) {
            $ulogin_id = Session::instance()
                ->get('ulogin_id');
            //привяжем OpenID-аккаунт к текущему аккаунту
            $ulogin = ORM::factory('Ulogin', $ulogin_id);
            if($ulogin->loaded()){
                $ulogin->user_id = \Meerkat\User\Me::id();
                $ulogin->save();
            }
            //удалим данные в сессии
            Session::instance()
                ->delete('ulogin_id');
            Session::instance()
                ->delete('ulogin');
        });
