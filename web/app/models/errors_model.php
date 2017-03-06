<?php
/**
 * Created by PhpStorm.
 * User: luwenwei
 * Date: 2017/3/2
 * Time: 15:58
 */

class Errors_model extends XIN_Model
{
    private $errno = 0;
    private $errmsg = '';
    private $data = array();

    const SUCC = 1;
    const ERROR = 2;

    const ERR_PARAM_MISSING = 10;

    const ERR_USER_EXIST = 100;
    const ERR_USER_NOT_EXIST = 101;
    const ERR_USER_VERIFY_ERROR = 102;

    const ERR_CARD_INVALID = 110;
    const ERR_DEADLINE_ERROR = 111;
    const ERR_DAY_TASK_ALREADY_EXIST = 112;

    const ERR_DB_ERROR = 200;


    protected static $ERRMSG_LIST = array(
        self::SUCC => '成功',
        self::ERROR => '错误',
        self::ERR_PARAM_MISSING => '缺少参数',
        self::ERR_USER_EXIST => '用户已存在',
        self::ERR_USER_NOT_EXIST => '用户不存在',
        self::ERR_DB_ERROR => 'DB出错',
        self::ERR_USER_VERIFY_ERROR => '用户验证失败',
        self::ERR_CARD_INVALID => '非法卡片',
        self::ERR_DEADLINE_ERROR => '截止时间格式错误',
    );

    public function __construct()
    {
        $this->errno = self::SUCC;
    }

    public function setCode($code)
    {
        $this->errno = $code;
        return $this;
    }

    public function setMsg($msg)
    {
        $this->errmsg = $msg;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getCode()
    {
        return $this->errno;
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function toArray()
    {
        if (empty($this->errmsg)) {
            $this->errmsg = isset(self::$ERRMSG_LIST[$this->errno]) ? self::$ERRMSG_LIST[$this->errno] : '';
        }
        return array(
            'code' => $this->errno,
            'message' => $this->errmsg,
            'data' => $this->data,
        );
    }
}