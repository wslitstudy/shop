<?php

namespace app\common\model;

use think\Cache;

/**
 * 地区模型
 * Class Region
 * @package app\common\model
 */
class Region extends BaseModel
{
    protected $name = 'region';
    protected $createTime = false;
    protected $updateTime = false;

    protected static $cacheAll;
    protected static $cacheTree;

    /**
     * 根据id获取地区名称
     * @param $id
     * @return string
     */
    public static function getNameById($id)
    {
        return $id > 0 ? self::getCacheAll()[$id]['name'] : '其他';
    }

    /**
     * 根据名称获取地区id
     * @param $name
     * @param int $level
     * @param int $pid
     * @return mixed
     */
    public static function getIdByName($name, $level = 0, $pid = 0)
    {
        $data = self::getCacheAll();
        foreach ($data as $item) {
            if ($item['name'] == $name && $item['level'] == $level && $item['pid'] == $pid)
                return $item['id'];
        }
        return 0;
    }

    /**
     * 获取所有地区(树状结构)
     * @return mixed
     */
    public static function getCacheTree()
    {
        empty(static::$cacheTree) && (static::$cacheTree = self::regionCache()['tree']);
        return static::$cacheTree;
    }

    /**
     * 获取所有地区
     * @return mixed
     */
    public static function getCacheAll()
    {
        empty(static::$cacheAll) && (static::$cacheAll = self::regionCache()['all']);
        return static::$cacheAll;
    }

    /**
     * 获取地区缓存
     * @return mixed
     */
    private static function regionCache()
    {
        if (!Cache::get('region')) {
            // 所有地区
            $all = $allData = self::useGlobalScope(false)->column('id, pid, name, level', 'id');
            // 格式化
            $tree = [];
            foreach ($allData as $pKey => $province) {
                if ($province['level'] == 1) {    // 省份
                    $tree[$province['id']] = $province;
                    unset($allData[$pKey]);
                    foreach ($allData as $cKey => $city) {
                        if ($city['level'] == 2 && $city['pid'] == $province['id']) {    // 城市
                            $tree[$province['id']]['city'][$city['id']] = $city;
                            unset($allData[$cKey]);
                            foreach ($allData as $rKey => $region) {
                                if ($region['level'] == 3 && $region['pid'] == $city['id']) {    // 地区
                                    $tree[$province['id']]['city'][$city['id']]['region'][$region['id']] = $region;
                                    unset($allData[$rKey]);
                                }
                            }
                        }
                    }
                }
            }
            Cache::tag('cache')->set('region', compact('all', 'tree'));
        }
        return Cache::get('region');
    }

}
