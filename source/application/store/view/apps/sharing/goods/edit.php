<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">拼团商品管理</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-form-label"> 商品ID </label>
                                <div class="am-u-sm-10">
                                    <div class="help-block am-padding-top-xs">
                                        <span class="x-f-13"><?= $model['goods_id'] ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-form-label"> 商品名称 </label>
                                <div class="am-u-sm-10">
                                    <div class="help-block am-padding-top-xs">
                                        <span class="x-f-13"><?= $model['goods']['goods_name'] ?></span>
                                    </div>
                                    <div class="uploader-list am-cf">
                                        <div class="file-item x-mt-10">
                                            <a href="<?= $model['goods']['image'][0]['file_path'] ?>"
                                               title="点击查看大图" target="_blank">
                                                <img src="<?= $model['goods']['image'][0]['file_path'] ?>">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-form-label form-require"> 商品信息 </label>
                                <div class="am-u-sm-10">
                                    <table class="spec-sku-tabel am-table am-table-bordered am-table-centered
                                     am-margin-bottom-xs am-text-nowrap">
                                        <tbody>
                                        <tr>
                                            <?php foreach ($specData['spec_attr'] as $val): ?>
                                                <th><?= $val['group_name'] ?></th>
                                            <?php endforeach; ?>
                                            <th>商家编码</th>
                                            <th>销售价</th>
                                            <th>划线价</th>
                                            <th>库存</th>
                                            <th class="form-require">
                                                <span>拼团价格</span>
                                            </th>
                                        </tr>
                                        <?php foreach ($specData['spec_list'] as $item): ?>
                                            <tr data-index="0" data-sku-id="<?= $item['spec_sku_id'] ?>">
                                                <?php foreach ($item['rows'] as $td): ?>
                                                    <td class="am-text-middle"
                                                        rowspan="<?= $td['rowspan'] ?>">
                                                        <?= $td['spec_value'] ?>
                                                    </td>
                                                <?php endforeach; ?>
                                                <td class="am-text-middle">
                                                    <?= $item['form']['goods_no'] ?>
                                                </td>
                                                <td class="am-text-middle">
                                                    <strong><?= $item['form']['goods_price'] ?></strong>
                                                </td>
                                                <td class="am-text-middle">
                                                    <?= $item['form']['line_price'] ?>
                                                </td>
                                                <td class="am-text-middle">
                                                    <?= $item['form']['stock_num'] ?>
                                                </td>
                                                <td class="am-text-middle">
                                                    <?php $group_price = isset($model['goods_info'][$item['spec_sku_id']])
                                                        ? $model['goods_info'][$item['spec_sku_id']]['group_price'] : '' ?>
                                                    <input class="x-w-80" type="number" min="0.01" required
                                                           name="goods[goods_info][<?= $item['spec_sku_id'] ?>][group_price]"
                                                           value="<?= $group_price ?>">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <div class="help-block">
                                        <small>注：参与拼团的商品，请注意及时同步主商品的价格和规格信息。</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-form-label form-require"> 成团人数 </label>
                                <div class="am-u-sm-10">
                                    <input type="number" min="2" class="tpl-form-input" name="goods[people]"
                                           value="<?= $model['people'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-form-label form-require"> 成团有效时长 </label>
                                <div class="am-u-sm-10">
                                    <input type="number" min="1" class="tpl-form-input" name="goods[group_time]"
                                           value="<?= $model['group_time'] ?>" required>
                                    <small>注：开团后的有效时间，单位：小时，超过时长则拼团失败</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="goods[goods_status]" value="10" data-am-ucheck
                                            <?= $model['goods_status'] == 10 ? 'checked' : '' ?>>
                                        上架
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="goods[goods_status]" value="20" data-am-ucheck
                                            <?= $model['goods_status'] == 20 ? 'checked' : '' ?>>
                                        下架
                                    </label>
                                </div>
                            </div>
                            <input type="hidden" name="goods[spec_type]" value="<?= $model['goods']['spec_type'] ?>">
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">保存
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
  $(function () {

    /**
     * 表单验证提交
     * @type {*}
     */
    $('#my-form').superForm();

  });
</script>
