<?php

namespace app\api\controller;

use app\api\model\Goods as GoodsModel;
use app\api\model\Cart as CartModel;
use app\common\service\qrcode\Goods as GoodsPoster;

/**
 * 商品控制器
 * Class Goods
 * @package app\api\controller
 */
class Goods extends Controller
{
    /**
     * 商品列表
     * @param $category_id
     * @param $search
     * @param $sortType
     * @param $sortPrice
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($category_id, $search, $sortType, $sortPrice)
    {
        $model = new GoodsModel;
        $list = $model->getList(10, $category_id, $search, $sortType, $sortPrice);
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 获取商品详情
     * @param $goods_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail($goods_id)
    {
        // 商品详情
        $detail = GoodsModel::detail($goods_id);
        if (!$detail || $detail['is_delete'] || $detail['goods_status']['value'] != 10) {
            return $this->renderError('很抱歉，商品信息不存在或已下架');
        }
        // 多规格商品sku信息
        $specData = $detail['spec_type'] == 20 ? $detail->getManySpecData($detail['spec_rel'], $detail['sku']) : null;
        // 购物车商品总数量
        if ($user = $this->getUser(false)) {
            $cart_total_num = (new CartModel($user['user_id']))->getTotalNum();
        }
        return $this->renderSuccess(compact('detail', 'cart_total_num', 'specData'));
    }

    /**
     * 获取推广二维码
     * @param $goods_id
     * @return arra
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function poster($goods_id)
    {
        // 商品详情
        $detail = GoodsModel::detail($goods_id);
        $Qrcode = new GoodsPoster($detail, $this->getUser(false));
        return $this->renderSuccess([
            'qrcode' => $Qrcode->getImage(),
        ]);
    }

}
