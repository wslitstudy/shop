<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget__order-detail widget-body am-margin-bottom-lg">
                    <div class="am-u-sm-12">
                        <?php
                        // 计算当前步骤位置
                        $progress = 2;
                        $detail['pay_status']['value'] == 20 && $progress += 1;
                        $detail['delivery_status']['value'] == 20 && $progress += 1;
                        $detail['receipt_status']['value'] == 20 && $progress += 1;
                        // $detail['order_status']['value'] == 30 && $progress += 1;
                        ?>
                        <ul class="order-detail-progress progress-<?= $progress ?>">
                            <li>
                                <span>下单时间</span>
                                <div class="tip"><?= $detail['create_time'] ?></div>
                            </li>
                            <li>
                                <span>付款</span>
                                <?php if ($detail['pay_status']['value'] == 20): ?>
                                    <div class="tip">
                                        付款于 <?= date('Y-m-d H:i:s', $detail['pay_time']) ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <li>
                                <span>发货</span>
                                <?php if ($detail['delivery_status']['value'] == 20): ?>
                                    <div class="tip">
                                        发货于 <?= date('Y-m-d H:i:s', $detail['delivery_time']) ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <li>
                                <span>收货</span>
                                <?php if ($detail['receipt_status']['value'] == 20): ?>
                                    <div class="tip">
                                        收货于 <?= date('Y-m-d H:i:s', $detail['receipt_time']) ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <li>
                                <span>完成</span>
                                <?php if ($detail['order_status']['value'] == 30): ?>
                                    <div class="tip">
                                        完成于 <?= date('Y-m-d H:i:s', $detail['receipt_time']) ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>

                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">基本信息</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>订单号</th>
                                <th>买家</th>
                                <th>订单金额</th>
                                <th>交易状态</th>
                                <?php if ($detail['pay_status']['value'] == 10 && $detail['order_status']['value'] == 10) : ?>
                                    <th>操作</th>
                                <?php endif; ?>
                            </tr>
                            <tr>
                                <td><?= $detail['order_no'] ?></td>
                                <td>
                                    <p><?= $detail['user']['nickName'] ?></p>
                                    <p class="am-link-muted">(用户id：<?= $detail['user']['user_id'] ?>)</p>
                                </td>
                                <td class="">
                                    <div class="td__order-price am-text-left">
                                        <ul class="am-avg-sm-2">
                                            <li class="am-text-right">订单总额：</li>
                                            <li class="am-text-right">￥<?= $detail['total_price'] ?> </li>
                                        </ul>
                                        <?php if ($detail['coupon_id'] > 0) : ?>
                                            <ul class="am-avg-sm-2">
                                                <li class="am-text-right">优惠券抵扣：</li>
                                                <li class="am-text-right">- ￥<?= $detail['coupon_price'] ?></li>
                                            </ul>
                                        <?php endif; ?>
                                        <ul class="am-avg-sm-2">
                                            <li class="am-text-right">运费金额：</li>
                                            <li class="am-text-right">+ ￥<?= $detail['express_price'] ?></li>
                                        </ul>
                                        <?php if ($detail['update_price']['value'] != '0.00') : ?>
                                            <ul class="am-avg-sm-2">
                                                <li class="am-text-right">后台改价：</li>
                                                <li class="am-text-right"><?= $detail['update_price']['symbol'] ?>
                                                    ￥<?= $detail['update_price']['value'] ?></li>
                                            </ul>
                                        <?php endif; ?>
                                        <ul class="am-avg-sm-2">
                                            <li class="am-text-right">实付款金额：</li>
                                            <li class="x-color-red am-text-right">
                                                ￥<?= $detail['pay_price'] ?></li>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                    <p>付款状态：
                                        <span class="am-badge
                                        <?= $detail['pay_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                <?= $detail['pay_status']['text'] ?></span>
                                    </p>
                                    <p>发货状态：
                                        <span class="am-badge
                                        <?= $detail['delivery_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                <?= $detail['delivery_status']['text'] ?></span>
                                    </p>
                                    <p>收货状态：
                                        <span class="am-badge
                                        <?= $detail['receipt_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                <?= $detail['receipt_status']['text'] ?></span>
                                    </p>
                                    <?php if ($detail['order_status']['value'] == 20 || $detail['order_status']['value'] == 21): ?>
                                        <p>订单状态：
                                            <span class="am-badge am-badge-warning"><?= $detail['order_status']['text'] ?></span>
                                        </p>
                                    <?php endif; ?>
                                </td>
                                <?php if ($detail['pay_status']['value'] == 10 && $detail['order_status']['value'] == 10) : ?>
                                    <td>
                                        <?php if (checkPrivilege('order/updateprice')): ?>
                                            <p class="am-text-center">
                                                <a class="j-update-price" href="javascript:void(0);"
                                                   data-order_id="<?= $detail['order_id'] ?>"
                                                   data-order_price="<?= $detail['total_price'] - $detail['coupon_price'] + $detail['update_price']['value'] ?>"
                                                   data-express_price="<?= $detail['express_price'] ?>">修改价格</a>
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">商品信息</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>商品名称</th>
                                <th>商品编码</th>
                                <th>重量(Kg)</th>
                                <th>单价</th>
                                <th>购买数量</th>
                                <th>商品总价</th>
                            </tr>
                            <?php foreach ($detail['goods'] as $goods): ?>
                                <tr>
                                    <td class="goods-detail am-text-middle">
                                        <div class="goods-image">
                                            <img src="<?= $goods['image']['file_path'] ?>" alt="">
                                        </div>
                                        <div class="goods-info">
                                            <p class="goods-title"><?= $goods['goods_name'] ?></p>
                                            <p class="goods-spec am-link-muted">
                                                <?= $goods['goods_attr'] ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td><?= $goods['goods_no'] ?: '--' ?></td>
                                    <td><?= $goods['goods_weight'] ?: '--' ?></td>
                                    <td>￥<?= $goods['goods_price'] ?></td>
                                    <td>×<?= $goods['total_num'] ?></td>
                                    <td>￥<?= $goods['total_price'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="6" class="am-text-right am-cf">
                                    <span class="am-fl">买家留言：<?= $detail['buyer_remark'] ?: '无' ?></span>
                                    <span class="am-fr">总计金额：￥<?= $detail['total_price'] ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">收货信息</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>收货人</th>
                                <th>收货电话</th>
                                <th>收货地址</th>
                            </tr>
                            <tr>
                                <td><?= $detail['address']['name'] ?></td>
                                <td><?= $detail['address']['phone'] ?></td>
                                <td>
                                    <?= $detail['address']['region']['province'] ?>
                                    <?= $detail['address']['region']['city'] ?>
                                    <?= $detail['address']['region']['region'] ?>
                                    <?= $detail['address']['detail'] ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($detail['pay_status']['value'] == 20): ?>
                        <div class="widget-head am-cf">
                            <div class="widget-title am-fl">付款信息</div>
                        </div>
                        <div class="am-scrollable-horizontal">
                            <table class="regional-table am-table am-table-bordered am-table-centered
                                am-text-nowrap am-margin-bottom-xs">
                                <tbody>
                                <tr>
                                    <th>应付款金额</th>
                                    <th>支付方式</th>
                                    <th>支付流水号</th>
                                    <th>付款状态</th>
                                    <th>付款时间</th>
                                </tr>
                                <tr>
                                    <td>￥<?= $detail['pay_price'] ?></td>
                                    <td>微信支付</td>
                                    <td><?= $detail['transaction_id'] ?: '--' ?></td>
                                    <td>
                                        <span class="am-badge
                                        <?= $detail['pay_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                <?= $detail['pay_status']['text'] ?></span>
                                    </td>
                                    <td>
                                        <?= $detail['pay_time'] ? date('Y-m-d H:i:s', $detail['pay_time']) : '--' ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!--  用户取消订单 -->
                    <?php if (checkPrivilege('order.operate/confirmcancel')): ?>
                        <?php if ($detail['pay_status']['value'] == 20 && $detail['order_status']['value'] == 21): ?>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl"><strong>用户取消订单</strong></div>
                            </div>
                            <div class="tips am-margin-bottom-sm am-u-sm-12">
                                <div class="pre">
                                    <p>当前买家已付款并申请取消订单，请审核是否同意，如同意则自动退回付款金额（微信支付原路退款）并关闭订单。</p>
                                </div>
                            </div>
                            <!-- 去审核 -->
                            <form id="cancel" class="my-form am-form tpl-form-line-form" method="post"
                                  action="<?= url('order.operate/confirmcancel', ['order_id' => $detail['order_id']]) ?>">
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">审核状态 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <div class="am-u-sm-9">
                                            <label class="am-radio-inline">
                                                <input type="radio" name="order[is_cancel]"
                                                       value="1"
                                                       data-am-ucheck
                                                       required>
                                                同意
                                            </label>
                                            <label class="am-radio-inline">
                                                <input type="radio" name="order[is_cancel]"
                                                       value="0"
                                                       data-am-ucheck
                                                       checked>
                                                拒绝
                                            </label>
                                        </div>

                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                        <button type="submit" class="j-submit am-btn am-btn-sm am-btn-secondary">
                                            确认审核
                                        </button>

                                    </div>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- 发货信息 -->
                    <?php if ($detail['pay_status']['value'] == 20 && $detail['order_status']['value'] != 20 && $detail['order_status']['value'] != 21): ?>
                        <div class="widget-head am-cf">
                            <div class="widget-title am-fl">发货信息</div>
                        </div>
                        <?php if ($detail['delivery_status']['value'] == 10): ?>
                            <?php if (checkPrivilege('order/delivery')): ?>
                                <!-- 去发货 -->
                                <form id="delivery" class="my-form am-form tpl-form-line-form" method="post"
                                      action="<?= url('order/delivery', ['order_id' => $detail['order_id']]) ?>">
                                    <div class="am-form-group">
                                        <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">物流公司 </label>
                                        <div class="am-u-sm-9 am-u-end am-padding-top-xs">
                                            <select name="order[express_id]"
                                                    data-am-selected="{btnSize: 'sm', maxHeight: 240}" required>
                                                <option value=""></option>
                                                <?php if (isset($express_list)): foreach ($express_list as $expres): ?>
                                                    <option value="<?= $expres['express_id'] ?>">
                                                        <?= $expres['express_name'] ?></option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                            <div class="help-block am-margin-top-xs">
                                                <small>可在 <a href="<?= url('setting.express/index') ?>" target="_blank">物流公司列表</a>
                                                    中设置
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">物流单号 </label>
                                        <div class="am-u-sm-9 am-u-end">
                                            <input type="text" class="tpl-form-input" name="order[express_no]" required>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                            <button type="submit" class="j-submit am-btn am-btn-sm am-btn-secondary">
                                                确认发货
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="am-scrollable-horizontal">
                                <table class="regional-table am-table am-table-bordered am-table-centered
                                    am-text-nowrap am-margin-bottom-xs">
                                    <tbody>
                                    <tr>
                                        <th>物流公司</th>
                                        <th>物流单号</th>
                                        <th>发货状态</th>
                                        <th>发货时间</th>
                                    </tr>
                                    <tr>
                                        <td><?= $detail['express']['express_name'] ?></td>
                                        <td><?= $detail['express_no'] ?></td>
                                        <td>
                                             <span class="am-badge
                                            <?= $detail['delivery_status']['value'] == 20 ? 'am-badge-success' : '' ?>">
                                                    <?= $detail['delivery_status']['text'] ?></span>
                                        </td>
                                        <td>
                                            <?= date('Y-m-d H:i:s', $detail['delivery_time']) ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script id="tpl-update-price" type="text/template">
    <div class="am-padding-top-sm">
        <form class="form-update-price am-form tpl-form-line-form" method="post"
              action="<?= url('order/updatePrice', ['order_id' => $detail['order_id']]) ?>">
            <div class="am-form-group">
                <label class="am-u-sm-3 am-form-label"> 订单金额 </label>
                <div class="am-u-sm-9">
                    <input type="number" min="0.00" class="tpl-form-input" name="order[update_price]"
                           value="{{ order_price }}">
                    <small>最终付款价 = 订单金额 + 运费金额</small>
                </div>
            </div>
            <div class="am-form-group">
                <label class="am-u-sm-3 am-form-label"> 运费金额 </label>
                <div class="am-u-sm-9">
                    <input type="number" min="0.00" class="tpl-form-input" name="order[update_express_price]"
                           value="{{ express_price }}">
                </div>
            </div>
        </form>
    </div>
</script>

<script>
    $(function () {

        /**
         * 修改价格
         */
        $('.j-update-price').click(function () {
            var $this = $(this);
            var data = $this.data();
            // var orderId = $(this).data('order_id');
            layer.open({
                type: 1
                , title: '订单价格修改'
                , area: '340px'
                , offset: 'auto'
                , anim: 1
                , closeBtn: 1
                , shade: 0.3
                , btn: ['确定', '取消']
                , content: template('tpl-update-price', data)
                , success: function (layero) {

                }
                , yes: function (index) {
                    // console.log('asdasd');
                    // 表单提交
                    $('.form-update-price').ajaxSubmit({
                        type: "post",
                        dataType: "json",
                        success: function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        }
                    });
                    layer.close(index);
                }
            });
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('.my-form').superForm();

    });
</script>
