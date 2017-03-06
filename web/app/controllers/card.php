<?php
/**
 * Created by PhpStorm.
 * User: luwenwei
 * Date: 2017/3/2
 * Time: 19:30
 */

class Card extends XIN_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('errors_model');
    }
    public function listAll()
    {
        $this->load->model('card_model');
        $data = $this->card_model->getCardList();
        $result = new Errors_model();
        echo $result->setData($data)->toJson();
        return;
    }
}