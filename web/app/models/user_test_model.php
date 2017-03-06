<?php
/**
 * Created by PhpStorm.
 * User: luwenwei
 * Date: 2017/3/2
 * Time: 13:19
 */
class User_test_model extends XIN_Model {
    public function __construct() {
        parent::__construct('user_test', 'userid', 'xin_walker');
    }

    public function test()
    {
        $results = $this->db->get('user_test')->result_array();
        var_dump($results);
    }
}