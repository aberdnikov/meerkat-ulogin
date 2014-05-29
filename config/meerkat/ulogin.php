<?php

    defined('SYSPATH') OR die('No direct access allowed.');

    $public = '/account-openid';

    return array
    (
        // Возможные значения: small, panel, window
        'type'         => 'panel',
        'url'          => array(
            'public_ulogin'          => $public,
            'public_ulogin_register' => '/account-openid-register',
            'ajax_ulogin_free'       => '/!/ajax/ulogin',
            'admin_ulogin_accounts'  => Kohana::$config->load('meerkat/admin.url.admin') . 'ulogin-accounts',
            'admin_ulogin_providers' => Kohana::$config->load('meerkat/admin.url.admin') . 'ulogin-providers',
        ),
        // на какой адрес придёт POST-запрос от uLogin
        'redirect_uri' => URL::site($public, 'http'),
        // Сервисы, выводимые сразу
        'providers'    => array(
            'vkontakte',
            'odnoklassniki',
            'facebook',
            'twitter',
            'google',
        ),
        // Выводимые при наведении
        'hidden'       => array(
            'mailru',
            'livejournal',
            'openid'
        ),
        // Эти поля используются для поля username в таблице users
        'username'     => array(
            'first_name',
            'last_name',
        ),
        // Обязательные поля
        'fields'       => array(
            'email',
            'photo',
            'photo_big',
        ),
        // Необязательные поля
        'optional'     => array(),
    );
