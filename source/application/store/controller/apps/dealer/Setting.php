<?php

namespace app\store\controller\apps\dealer;

use app\store\controller\Controller;
use app\store\model\dealer\Setting as SettingModel;

/**
 * 分销设置
 * Class Setting
 * @package app\store\controller\apps\dealer
 */
class Setting extends Controller
{
    /**
     * 分销设置
     * @return array|mixed
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        if (!$this->request->isAjax()) {
            $data = SettingModel::getAll();
            return $this->fetch('index', compact('data'));
        }
        $model = new SettingModel;
        if ($model->edit($this->postData('setting'))) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 分销海报
     * @return array|mixed
     * @throws \think\exception\PDOException
     */
    public function qrcode()
    {
        if (!$this->request->isAjax()) {
            $data = SettingModel::getItem('qrcode');
            return $this->fetch('qrcode', [
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE)
            ]);
        }
        $model = new SettingModel;
        if ($model->edit(['qrcode' => $this->postData('qrcode')])) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}