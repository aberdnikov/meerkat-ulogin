<?php

    use Meerkat\Core\Page_TplVar;
    use Meerkat\Widget\Widget_Alert;
    use Meerkat\Widget\Widget_Breadcrumbs;
    use Meerkat\Core\Page_Layout;
    use Meerkat\Core\Map;
    use Meerkat\Form\Form;
    use Meerkat\Html\Fieldset;
    use Meerkat\Twig\Twig;


    class Kohana_Controller_Public_Ulogin_Register extends Controller_Index {

        /**
         * @var ORM
         */
        protected $_model;

        function check_email($email) {
            return $this->_model->unique('email', $email);
        }

        function check_login($login) {
            return $this->_model->unique('login', $login);
        }

        public function action_index() {
            Meerkat\Core\Page_Layout::instance()
                ->template(true);
            \Meerkat\Core\Seo::instance()
                ->add_breadcrumb('Регистрация', Kohana::$config->load('meerkat/user.url.public_register'));
            $this->_model = ORM::factory('User');
            $form         = Form::factory('register');
            $email        = $this->field_email($form);
            $login        = $this->field_login($form);
            if (Kohana::$config->load('meerkat/user.url.public_users')) {
                $login->set_prepend('http://' . $_SERVER['HTTP_HOST'] . Kohana::$config->load('meerkat/user.url.public_users'));
            }
            $this->field_submit($form);
            if ($form
                ->get_element()
                ->validate()
            ) {
                $this->save($form);
            }
            Page_TplVar::instance()
                ->set_body($form->render());
        }

        function field_email($form) {
            return $form
                ->add_email('email')
                ->set_label('Электронный адрес')
                ->rule_callback(array($this,
                    'check_email'), 'На проекте уже есть участник с таким электронным адресом')
                ->set_prepend(\Meerkat\Html\Icon_Famfamfam::icon(\Meerkat\Html\Icon_Famfamfam::_EMAIL))
                ->add_class('form-control')
                ->rule_required()
                ->set_desc('Обязательно указывайте свой почтовый ящик, к которому у вас есть доступ, так как на него придет письмо с паролем для входа');;
        }

        function field_login($form) {
            return $form
                ->add_text('login')
                ->set_label('Логин')
                ->set_append(\Meerkat\Html\Icon_Famfamfam::icon(\Meerkat\Html\Icon_Famfamfam::_USER))
                ->add_class('form-control')
                ->set_example('groove')
                ->set_example('alexey')
                ->rule_callback(array($this,
                    'check_login'), 'На проекте уже есть участник с таким логином')
                ->set_desc('Допустимы латинские буквы, цифры, тире и знак подчеркивания. <br />Это ваш идентификатор на проекте, в дальнейшем его поменять будет нельзя')
                ->rule_regexp('/^[a-zA-Z0-9-]+$/', 'Допускаются только латинские буквы, цифры, тире и знак подчеркивания')
                ->rule_required();

        }

        function field_submit($form) {
            $gr = $form->add_actions_group();
            $gr
                ->add_submit('s')
                ->add_class('btn btn-success btn-lg btn-block')
                ->set_label('Создать аккаунт!');
            $links = array();
            $event = new \sfEvent(null, 'Controller_Public_Register::links');
            \Meerkat\Event\Event::dispatcher()
                ->filter($event, $links);
            $links = $event->getReturnValue();
            if (count($links)) {
                $gr->add_static('<hr class="soften soften-sm">' . implode(' или ', $links));
            }

        }

        function save($form) {
            $values              = $form
                ->get_element()
                ->getValue();
            $login               = Arr::get($values, 'login');
            $email               = Arr::get($values, 'email');
            $user                = ORM::factory('User');
            $user->email         = $email;
            $user->username      = $login;
            $user->login         = $login;
            $user->regdate       = date('Y-m-d H:i:s');
            $password            = mb_substr(md5(microtime(true)), 3, 6);
            $activate_code       = mb_substr(md5(microtime(true)), 12, 4);
            $user->activate_code = $activate_code;
            $user->password      = $password;
            $user->save();
            $event = new \sfEvent(null, 'Controller_Public_Register::done', array(
                'user'          => $user,
                'password'      => $password,
                'activate_code' => $activate_code,
            ));
            \Meerkat\Event\Event::dispatcher()
                ->notify($event);
            //если не надо подтверждать мыло - авторизуем юзера и кинем его в кабинет
            if (!Kohana::$config->load('meerkat/user.require_confirm')) {
                Auth::instance()
                    ->force_login($login);
                $this->redirect_msg_success('Вы успешно зарегистрировались', Kohana::$config->load('meerkat/user.url.account'));
            }
            else {
                $this->redirect_msg_success('Вы успешно зарегистрировались, получите почту на указанный при регистрации почтовый ящик - туда был отправлен код активации аккаунта', Kohana::$config->load('meerkat/user.url.public_activate'));
            }
        }

    }