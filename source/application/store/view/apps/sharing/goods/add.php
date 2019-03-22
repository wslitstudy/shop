<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加拼团商品</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 选择商品 </label>
                                <div class="am-u-sm-9">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="j-add upload-file am-btn am-btn-secondary am-radius">
                                            点击选择
                                        </button>
                                    </div>
                                    <div class="help-block">
                                        <small>请选择参与拼团的商品，仅能选择一个</small>
                                    </div>
                                    <div class="goods-info"></div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">下一步
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

<!-- 选择商品 -->
<script id="tpl-goods-item" type="text/template">
    <div class="uploader-list am-cf">
        <div class="file-item x-mt-10">
            <a href="{{ data.image }}"
               title="点击查看大图" target="_blank">
                <img src="{{ data.image }}">
            </a>
            <input type="hidden" name="goods_id" value="{{ data.goods_id }}">
        </div>
    </div>
    <div class="goods-name am-padding-xs x-f-13">
        {{ data.goods_name }}
    </div>
</script>

<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
  $(function () {

    /**
     * 表单验证提交
     * @type {*}
     */
    $('#my-form').superForm();

    /**
     * 新增拼团商品
     */
    $('.j-add').selectData({
      title: '选择商品',
      uri: 'goods/lists&status=10',
      dataIndex: 'goods_id',
      done: function (data) {
        $('.goods-info').html(
          template('tpl-goods-item', {data: data[0]})
        );
      }
    });

  });
</script>
