<?php
/**
 * Created by PhpStorm.
 * User: luwenwei
 * Date: 2017/3/2
 * Time: 15:39
 */

class Card_model extends XIN_Model
{
    protected static $TASK_LIST = array(
        1 => array(
            'card_id' => 1,
            'order' => 1,
            'card_type' => 1,
            'card_name' => '小目标',
            'min_distance' => '1000',
            'card_price' => '2',
        ),
        2 => array(
            'card_id' => 2,
            'order' => 2,
            'card_type' => 1,
            'card_name' => '小挑战',
            'min_distance' => '3000',
            'card_price' => '10',
        ),
        3 => array(
            'card_id' => 3,
            'order' => 3,
            'card_type' => 1,
            'card_name' => '终极目标',
            'min_distance' => '5000',
            'card_price' => '99',
        ),
    );

    public function __construct()
    {
        parent::__construct('card_task', 'task_id', 'xin_walker');
    }

    public function checkCardId($id)
    {
        foreach (self::$TASK_LIST as $task) {
            if ($task['car_id'] == $id) {
                return true;
            }
        }
        return false;
    }

    public function getCardInfo($id)
    {
        foreach (self::$TASK_LIST as $task) {
            if ($task['card_id'] == $id) {
                return $task;
            }
        }
        return array();
    }
    public function getCardList()
    {
        return self::$TASK_LIST;
    }
}