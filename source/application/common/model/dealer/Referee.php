<?php

namespace app\common\model\dealer;

use app\common\model\BaseModel;

/**
 * 分销商推荐关系模型
 * Class Referee
 * @package app\common\model\dealer
 */
class Referee extends BaseModel
{
    protected $name = 'dealer_referee';

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\api\model\User');
    }

    /**
     * 关联分销商用户表
     * @return \think\model\relation\BelongsTo
     */
    public function dealer()
    {
        return $this->belongsTo('User');
    }

}