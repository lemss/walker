<?php
/**
 * Created by PhpStorm.
 * User: luwenwei
 * Date: 2017/3/2
 * Time: 21:20
 */

class Userdata_model extends XIN_Model
{
    protected static $TABLE = 'user_data';

    public function __construct()
    {
        parent::__construct(self::$TABLE, 'id', 'xin_walker');
    }

    public function upload($data)
    {
        $this->load->model('card_task_model');
        $this->load->model('task_reward_model');
        if (!isset($data['uid']) || !isset($data['day']) || !isset($data['distance']))
        {
            return false;
        }
        //写入用户数据信息，数据可以多次提交，后续提交覆盖原先数据
        $id = $this->insertData(array(
            'userid' => $data['uid'],
            'day' => $data['day'],
            'distance' => $data['distance'],
            'is_last' => isset($data['is_last']) && $data['is_last'] == 1 ? $data['is_last'] : 0,
        ));
        if ($id === false) {
            return false;
        }
        //如果正好某天有任务，且是未完成或者开始完成的话，则继续处理；否则停止；
        $unstopStatus = array(
            Card_task_model::TASK_STATUS_IS_GOING,
            Card_task_model::TASK_STATUS_NO_START,
            Card_task_model::TASK_STATUS_STOP_BUT_CONFIRM
        );
        $dayDeadline = strtotime( $data['day'] . " 23:59:59");//var_dump($dayDeadline, $data['uid']);
        $taskInfo = $this->db->where('deadline', $dayDeadline)->where('userid', $data['uid'])
            ->where_in('task_status', $unstopStatus)
            ->where('pay_status', Card_task_model::TASK_PAY_STATUS_PAYED)
            ->get('card_task')->row_array();
        if (empty($taskInfo)) {
            return true;
        }
        if ($data['distance'] <= 1) {
            return false;
        }
        //只有完成既定目标 才能奖励用户；否则不再这个阶段去做奖励
        $amountPercent = $data['distance'] > $taskInfo['distance'] ? 1 : round($data['distance'] / $taskInfo['distance'], 2, PHP_ROUND_HALF_DOWN);
        $amount = $taskInfo['amount'] * $amountPercent;
        $type = $amountPercent < 1 ? Task_reward_model::REWARD_TYPE_PART : Task_reward_model::REWARD_TYPE_FULL;
        if ($amountPercent >= 1 || $data['is_last'] == 1) {
            //如果没有奖励过 insert-reward;update-task;
            //如果已经奖励过 不再修改状态，只更新步数
            $rewardDb = $this->db->where('userid', $data['uid'])->where('task_id', $taskInfo['task_id'])->get('task_reward')->row_array();
            if ($rewardDb) {
                //更新步数返回
                $this->db->update('task_reward', array('actual_distance' => $data['distance']), array('reward_id' => $rewardDb['reward_id']));
                return;
            }
            $rewardInfo = array(
                'task_id' => $taskInfo['task_id'],
                'userid' => $data['uid'],
                'actual_distance' => $data['distance'],
                'reward_type' => $type,//算出来的
                'reward_status' => Task_reward_model::REWARD_STATUS_PAYED, //已支付
                'reward_amount' => $amount, //算出来的
                'card_id' => $taskInfo['card_id'],
                'deadline' => $taskInfo['deadline'],
            );
            $this->db->insert('task_reward', $rewardInfo);
            $taskStatus = $amountPercent >= 1 ? Card_task_model::TASK_STATUS_STOP_FINISHED : Card_task_model::TASK_STATUS_STOP_PART_FINISHED;
            $this->db->update('card_task', array('task_status' => $taskStatus), array('task_id' => $taskInfo['task_id']));
        }
        return true;
    }

    public function insertData($data)
    {
        $row = $this->db->where('day', $data['day'])->where('userid', $data['userid'])->get(self::$TABLE)->row_array();
        if ($row) {
            if ($row['is_last'] == 1) {
                return true;
            }
            return $this->db->update(self::$TABLE, array('distance' => $data['distance']), array('userid' => $data['userid'], 'day' => $data['day']));
        }

        $ret = $this->db->insert(self::$TABLE, $data);
        if ($ret) {
            return $this->db->insert_id();
        }
        return false;
    }
}