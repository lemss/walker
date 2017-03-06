<?php
/**
 * Created by PhpStorm.
 * User: luwenwei
 * Date: 2017/3/2
 * Time: 18:22
 */

class Paopao extends XIN_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function home()
    {
        $this->render('/paopao/home');
    }
}