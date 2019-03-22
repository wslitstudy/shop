<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf"><?= $title ?></div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <input type="hidden" name="dataType" value="<?= $dataType ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <div class="am-btn-toolbar">
                                        <div class="am-btn-group am-btn-group-xs">
                                            <?php if (checkPrivilege('order.operate/export')): ?>
                                                <a class="j-export am-btn am-btn-success am-radius"
                                                   href="javascript:void(0);">
                                                    <i class="iconfont icon-daochu am-margin-right-xs"></i>订单导出
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('order.operate/batchdelivery') && $dataType == 'delivery'): ?>
                                                <a class="j-export am-btn am-btn-secondary am-radius"
                                                   href="<?= url('order.operate/batchdelivery') ?>">
                                                    <i class="iconfont icon-daoru am-margin-right-xs"></i>批量发货
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="start_time"
                                               class="am-form-field"
                                               value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="end_time"
                                               class="am-form-field"
                                               value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="order_no"
                                                   placeholder="请输入订单号"
                                                   value="<?= $request->get('order_no') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="order-list am-scrollable-horizontal am-u-sm-12 am-margin-top-xs">
                        <table width="100%" class="am-table am-table-centered
                        am-text-nowrap am-margin-bottom-xs">
                            <thead>
                            <tr>
                                <th width="30%" class="goods-detail">商品信息</th>
                                <th width="10%">单价/数量</th>
                                <th width="15%">实付款</th>
                                <th>买家</th>
                                <th>交易状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $order): ?>
                                <tr class="order-empty">
                                    <td colspan="6"></td>
                                </tr>
                                <tr>
                                    <td class="am-text-middle am-text-left" colspan="6">
                                        <span class="am-margin-right-lg"> <?= $order['create_time'] ?></span>
                                        <span class="am-margin-right-lg">订单号：<?= $order['order_no'] ?></span>
                                    </td>
                                </tr>
                                <?php $i = 0;
                                foreach ($order['goods'] as $goods): $i++; ?>
                                    <tr>
                                        <td class="goods-detail am-text-middle">
                                            <div class="goods-image">
                                                <img src="<?= $goods['image']['file_path'] ?>" alt="">
                                            </div>
                                            <div class="goods-info">
                                                <p class="goods-title"><?= $goods['goods_name'] ?></p>
                                                <p class="goods-spec am-link-muted"><?= $goods['goods_attr'] ?></p>
                                            </div>
                                        </td>
                                        <td class="am-text-middle">
                                            <p>￥<?= $goods['goods_price'] ?></p>
                                            <p>×<?= $goods['total_num'] ?></p>
                                        </td>
                                        <?php if ($i === 1) : $goodsCount = count($order['goods']); ?>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <p>￥<?= $order['pay_price'] ?></p>
                                                <p class="am-link-muted">(含运费：￥<?= $order['express_price'] ?>)</p>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <p><?= $order['user']['nickName'] ?></p>
                                                <p class="am-link-muted">(用户id：<?= $order['user']['user_id'] ?>)</p>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <p>付款状态：
                                                    <span class="am-badge
                                                <?= $order['pay_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                        <?= $order['pay_status']['text'] ?></span>
                                                </p>
                                                <p>发货状态：
                                                    <span class="am-badge
                                                <?= $order['delivery_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                        <?= $order['delivery_status']['text'] ?></span>
                                                </p>
                                                <p>收货状态：
                                                    <span class="am-badge
                                                <?= $order['receipt_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                        <?= $order['receipt_status']['text'] ?></span>
                                                </p>
                                                <?php if ($order['order_status']['value'] == 20 || $order['order_status']['value'] == 21): ?>
                                                    <p>订单状态：
                                                        <span class="am-badge am-badge-warning"><?= $order['order_status']['text'] ?></span>
                                                    </p>
                                                <?php endif; ?>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <div class="tpl-table-black-operation">
                                                    <?php if (checkPrivilege('order/detail')): ?>
                                                        <a class="tpl-table-black-operation-green"
                                                           href="<?= url('order/detail', ['order_id' => $order['order_id']]) ?>">
                                                            订单详情</a>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege(['order/detail', 'order/delivery'])): ?>
                                                        <?php if ($order['pay_status']['value'] == 20
                                                            && $order['delivery_status']['value'] == 10
                                                            && $order['order_status']['value'] != 20
                                                            && $order['order_status']['value'] != 21
                                                        ): ?>
                                                            <a class="tpl-table-black-operation"
                                                               href="<?= url('order/detail#delivery',
                                                                   ['order_id' => $order['order_id']]) ?>">去发货</a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege(['order/detail', 'order.operate/confirmcancel'])): ?>
                                                        <?php if ($order['order_status']['value'] == 21): ?>
                                                            <a class="tpl-table-black-operation-del"
                                                               href="<?= url('order/detail#cancel',
                                                                   ['order_id' => $order['order_id']]) ?>">去审核</a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

    $(function () {

        /**
         * 订单导出
         */
        $('.j-export').click(function () {
            var data = {};
            var formData = $('#form-search').serializeArray();
            $.each(formData, function () {
                this.name !== 's' && (data[this.name] = this.value);
            });
            window.location = "<?= url('order.operate/export') ?>" + '&' + $.urlEncode(data);
        });

    });

</script>

