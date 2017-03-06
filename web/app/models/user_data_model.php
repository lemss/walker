<?php
/**
 * Created by PhpStorm.
 * User: luwenwei
 * Date: 2017/3/2
 * Time: 15:38
 */

class User_data_model extends XIN_Model
{
    public function __construct()
    {
        parent::__construct('user_data', 'id', 'xin_walker');
    }
}