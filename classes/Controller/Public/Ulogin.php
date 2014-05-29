<?php

    class Controller_Public_Ulogin extends Controller_Index {

        function action_index() {
            if ($url = Arr::get($_GET, 'return')) {
                Session::instance()
                    ->set('return', $url);
            }
            if (\Meerkat\User\Me::id()) {
                Meerkat\Core\Seo::instance()
                    ->add_breadcrumb('Управление подключенными аккаунтами социальных сетей', Kohana::$config->load('meerkat/ulogin.url.public_ulogin'));
            }
            else {
                Meerkat\Core\Seo::instance()
                    ->add_breadcrumb('Вход', Kohana::$config->load('meerkat/ulogin.url.public_ulogin'));
            }
            Meerkat\Core\Page_Layout::instance()
                ->template(true);
            //Auth::instance()->logout(true,true);
            \Meerkat\Core\Page_TplVar::instance()
                ->set('ulogin', Ulogin::factory()
                    ->render());
            if (0) {
                Debug::info(session_save_path());
                Debug::info(Kohana::$config
                    ->load('auth')
                    ->as_array());
                Debug::stop($_SESSION);
            }
            if (!empty($_POST['token'])) {
                try {
                    Ulogin::factory()
                        ->login();
                }
                catch (Exception $exc) {
                    //Meerkat\Widget\Widget_Alert::factory('<strong>Ошибка при входе:</strong> "' . $exc->getMessage() . '" - попробуйте еще раз')
                    Meerkat\Widget\Widget_Alert::factory('<strong>Ошибка при входе:</strong> "' . Kohana_Exception::text($exc) . '" - попробуйте еще раз')
                        ->as_error()
                        ->put();
                    $this->redirect($_SERVER['PHP_SELF']);
                }
                //если авторизовался, но требуется завершение регистрации
                $data = Session::instance()
                    ->get('ulogin');
                //Debug::stop($data);
                if ($data) {
                    //редиректим на второй шаг регистрации через OpenID
                    $this->redirect(Kohana::$config->load('meerkat/user.url.public_register'));
                }
                Meerkat\Widget\Widget_Alert::factory('Вход произведен, ' . \Meerkat\User\Me::username())
                    ->as_success()
                    ->put();
                $url = Session::instance()
                    ->get_once('return', '/');
                $this->redirect($url);
            }
        }

    }