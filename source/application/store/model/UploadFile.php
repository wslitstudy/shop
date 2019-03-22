<?php

namespace app\store\model;

use think\Request;
use app\common\model\UploadFile as UploadFileModel;

/**
 * 文件库模型
 * Class UploadFile
 * @package app\store\model
 */
class UploadFile extends UploadFileModel
{
    /**
     * 获取列表记录
     * @param $group_id
     * @param string $file_type
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($group_id, $file_type = 'image')
    {
        if ($group_id != -1) {
            $this->where(compact('group_id'));
        }
        return $this->where([
            'file_type' => $file_type,
            'is_user' => 0,
            'is_delete' => 0
        ])->order(['file_id' => 'desc'])
            ->paginate(32, false, [
                'query' => Request::instance()->request()
            ]);
    }

    /**
     * 批量软删除
     * @param $fileIds
     * @return $this
     */
    public function softDelete($fileIds)
    {
        return $this->where('file_id', 'in', $fileIds)->update(['is_delete' => 1]);
    }

    /**
     * 批量移动文件分组
     * @param $group_id
     * @param $fileIds
     * @return $this
     */
    public function moveGroup($group_id, $fileIds)
    {
        return $this->where('file_id', 'in', $fileIds)->update(compact('group_id'));
    }

}
