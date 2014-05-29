<?php

    defined('SYSPATH') or die('No direct script access.');

    class Kohana_Ulogin {

        protected static $_used_id = array();
        protected $config = array(
            // Возможные значения: small, panel, window
            'type'         => 'panel',
            // на какой адрес придёт POST-запрос от uLogin
            'redirect_uri' => null,
            // Сервисы, выводимые сразу
            'providers'    => array(
                'vkontakte',
                'facebook',
                'twitter',
                'google',
            ),
            // Выводимые при наведении
            'hidden'       => array(
                'odnoklassniki',
                'mailru',
                'livejournal',
                'openid'
            ),
            // Эти поля используются для значения поля username в таблице users
            'username'     => array(
                'first_name',
                'last_name',
            ),
            // Обязательные поля
            'fields'       => array(
                'email',
            ),
            // Необязательные поля
            'optional'     => array(
                'photo',
                'photo_big',
                'city',
                'country',
            ),
        );

        public function __construct(array $config = array()) {
            $this->config = array_merge($this->config, Kohana::$config
                ->load('meerkat/ulogin')
                ->as_array(), $config);

            if ($this->config['redirect_uri'] === null) {
                $this->config['redirect_uri'] = Request::initial()
                    ->url(true);
            }
        }

        public static function factory(array $config = array()) {
            return new Ulogin($config);
        }

        public function __toString() {
            try {
                return $this->render();
            }
            catch (Exception $e) {
                Kohana_Exception::handler($e);
                return '';
            }
        }

        public function render() {
            $params =
                'display=' . $this->config['type'] .
                '&fields=' . implode(',', array_merge($this->config['username'], $this->config['fields'])) .
                '&providers=' . implode(',', $this->config['providers']) .
                '&hidden=' . implode(',', $this->config['hidden']) .
                '&redirect_uri=' . $this->config['redirect_uri'] .
                '&optional=' . implode(',', $this->config['optional']);

            $view = \Meerkat\Twig\Twig::from_template('!/widgets/ulogin')
                ->set('cfg', $this->config)
                ->set('params', $params);
            do {
                $uniq_id = "uLogin_" . rand();
            } while (in_array($uniq_id, self::$_used_id));

            self::$_used_id[] = $uniq_id;

            $view->set('uniq_id', $uniq_id);

            return $view->render();
        }

        public function login() {
            if (empty($_POST['token'])) {
                throw new Kohana_Exception('Empty token.');
            }

            if (!($domain = parse_url(URL::base(), PHP_URL_HOST))) {
                $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
            }

            $s    = Request::factory('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $domain)
                ->execute()
                ->body();
            $user = json_decode($s, true);
            //\Debug::stop($user);
            $identity = Arr::get($user,'identity');
            if(!$identity){
                throw new Kohana_Exception('Сервер не возвратил вашего идентификатора, попробуйте еще раз');
            }
            $ulogin             = ORM::factory('Ulogin', array('identity' => $identity));
            $network            = ORM::factory('Ulogin_Network')
                ->get_or_create(array(
                'value' => Arr::get($user, 'network')
            ));
            $user['network_id'] = $network->pk();

            if (!$ulogin->loaded()) {
                if (!Kohana::$config->load('meerkat/user.can.public_register')) {
                    throw new Kohana_Exception('Регистрация на проекте закрыта. Вам необходимо сперва авторизоваться, а уже затем прикрепить OpenID аккаунт для упрощения входа в будущем');
                }
                //если авторизован - прикрепим OpenID-аккаунт к текущему аккаунту
                $user['username'] = '';
                foreach ($this->config['username'] as $part_of_name)
                    $user['username'] .= (empty($user[$part_of_name]) ? '' : (' ' . $user[$part_of_name]));

                $user['username'] = trim($user['username']);
                if (\Meerkat\User\Me::id()) {
                    $user['user_id'] = \Meerkat\User\Me::id();
                    $ulogin
                        ->values($user, array(
                        'username',
                        'email',
                        'user_id',
                        'identity',
                        'network_id',
                        'photo',
                        'photo_big',
                    ))
                        ->create();
                }
                else {
                    //\Debug::stop($data);

                    $cfg_fields = array_merge($this->config['fields'], $this->config['optional']);
                    foreach ($cfg_fields as $field) {
                        if (!empty($user[$field])) {
                            $data[$field] = $user[$field];
                        }
                    }
                    //Debug::stop($user);
                    $user['user_id'] = 0;
                    $ulogin
                        ->values($user, array(
                        'username',
                        'email',
                        'user_id',
                        'identity',
                        'network_id',
                        'photo',
                        'photo_big',
                    ))
                        ->create();
                    Session::instance()
                        ->set('ulogin', $data);
                    Session::instance()
                        ->set('ulogin_id', $ulogin->pk());
                }
            }
            else {
                if($ulogin->user_id){
                    Auth::instance()
                        ->force_login($ulogin->user);
                } else {
                    Session::instance()
                        ->set('ulogin', $ulogin->as_array());
                    Session::instance()
                        ->set('ulogin_id', $ulogin->pk());
                }
            }
        }

        public function mode() {
            return !empty($_POST['token']);
        }

    }