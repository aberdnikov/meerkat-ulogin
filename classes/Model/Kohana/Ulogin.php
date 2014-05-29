<?php

defined('SYSPATH') or die('No direct access allowed.');

class Model_Kohana_Ulogin extends ORM {

    protected $_load_with = array(
        'user',
        'network',
    );
    protected $_belongs_to = array(
        'user' => array(),
        'network' => array(
            'model' => 'Ulogin_Network',
            'foreign_key' => 'network_id',
        ),
    );

    public function rules() {
        return array(
            'identity' => array(
                array('not_empty'),
                array('max_length', array(':value', 255)),
                array(array($this, 'unique'), array('identity', ':value')),
            ),
        );
    }

}