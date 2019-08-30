<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/19
 * Time: 16:52
 */
$urlManager = Yii::$app->urlManager;
$this->title = '秒杀商品编辑';
$this->params['active_nav_group'] = 10;
?>
<style>
    .open-date-delete {
        display: inline-block;
        color: #fff;
        font-weight: bolder;
        background: #ff6461;
        width: 12px;
        text-align: center;
        height: 12px;
        line-height: 12px;
        padding: 0;
        border-radius: .15rem;

    }

    .open-date-delete:hover {
        background: #ff4544;
        color: #fff;
        text-decoration: none;
    }

    .open-date-delete:active {
        background: #da3b3a;
        text-decoration: none;
    }
</style>
<div class="panel mb-3" id="app">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <form class="auto-form" method="post">
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">秒杀商品</label>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <input class="form-control search-goods-name" readonly>
                        <input class="search-goods-id" type="hidden" name="goods_id">
                        <span class="input-group-btn">
                                <a href="javascript:" class="btn btn-secondary search-goods" data-toggle="modal"
                                   data-target="#searchGoodsModal">选择商品</a>
                            </span>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">价格、数量</label>
                </div>
                <div class="col-sm-6">
                    <div v-if="goods==null" class="text-muted" style="padding-top: calc(.5rem - 1px * 2)">请先选择商品
                    </div>
                    <template v-else>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th v-bind:colspan="goods.attr[0].attr_list.length">规格</th>
                                <th>售价</th>
                                <th>秒杀价</th>
                                <th>库存</th>
                                <th>秒杀数量</th>
                            </tr>
                            </thead>
                            <tr v-for="attr_row in goods.attr">
                                <td v-for="attr in attr_row.attr_list">{{attr.attr_name}}</td>
                                <td>{{attr_row.price==0?goods.price:attr_row.price}}</td>
                                <td><input style="width: 4rem" type="number"
                                           class="form-control form-control-sm check-goods-data"
                                           v-model="attr_row.seckill_price"
                                           step="0.01" min="0.01"
                                           v-bind:max="attr_row.price==0?goods.price:attr_row.price"></td>
                                <td>{{attr_row.num}}</td>
                                <td><input style="width: 4rem" type="number"
                                           class="form-control form-control-sm check-goods-data"
                                           v-model="attr_row.seckill_num" step="1"
                                           min="1" v-bind:max="attr_row.num"></td>
                            </tr>
                        </table>
                    </template>
                </div>
            </div>

            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">开放时间</label>
                </div>
                <div class="col-sm-6">
                    <?php $seckill->open_time = json_decode($seckill->open_time, true); ?>
                    <?php if (is_array($seckill->open_time) && count($seckill->open_time)):foreach ($seckill->open_time as $i): $i = intval($i) ?>
                        <label class="custom-control custom-checkbox">
                            <input name="open_time[]" type="checkbox"
                                   class="custom-control-input open-time-checkbox"
                                   value="<?= $i ?>">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description"><?= $i < 10 ? '0' . $i : $i ?>
                                :00~<?= $i < 10 ? '0' . $i : $i ?>:59</span>
                        </label>
                    <?php endforeach; else: ?>
                        <div class="text-muted">商城未设置秒杀开放时间，请<a
                                    href="<?= $urlManager->createUrl(['mch/seckill/index']) ?>">前往设置</a></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">限购数量</label>
                </div>
                <div class="col-sm-6">
                    <div class="form-inline">
                        <input name="buy_max"
                            <?= $model->buy_max != 0 ? null : 'checked' ?>
                               class="form-control mr-3"
                               type="number" step="1" min="0" placeholder="请填写整数值"
                               value="0">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input no-buy-max">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">不限购</span>
                        </label>
                    </div>
                    <div class="text-muted fs-sm">每个订单限制购买数量，填写0表示不限购，默认不限购</div>
                </div>
            </div>

            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">开放日期</label>
                </div>
                <div class="col-sm-6">
                    <span v-for="(item,index) in open_date" class="mr-3 badge badge-default">
                        <span>{{item}}</span>
                        <a href="javascript:" class="open-date-delete" v-bind:index="index">×</a>
                    </span>
                    <a class="badge badge-primary" href="javascript:" id="quickAddDate">+&nbsp;添加</a>
                    <a class="badge badge-primary" href="javascript:" data-toggle="modal"
                       data-target="#addDateModal">+&nbsp;批量添加</a>
                    <div class="text-muted fs-sm mt-1">只可选择今日起一个月内的时间</div>
                </div>
            </div>

            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary seckill-submit-btn" href="javascript:">保存</a>
                </div>
            </div>
        </form>


        <!-- Modal -->
        <div class="modal fade" data-backdrop="static" id="searchGoodsModal" tabindex="-1" role="dialog"
             aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">查找商品</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="<?= $urlManager->createUrl(['mch/seckill/goods-search']) ?>"
                              class="input-group  goods-search-form" method="get">
                            <input name="keyword" class="form-control" placeholder="商品名称">
                            <span class="input-group-btn">
                        <button class="btn btn-secondary submit-btn">查找</button>
                    </span>
                        </form>
                        <div v-if="goodsList==null" class="text-muted text-center p-5">请输入商品名称查找商品</div>
                        <template v-else>
                            <div v-if="goodsList.length==0" class="text-muted text-center p-5">未查找到相关商品</div>
                            <template v-else>
                                <div class="goods-item row mt-3 mb-3" v-for="(item,index) in goodsList">
                                    <div class="col-8">
                                        <div style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis">
                                            {{item.name}}
                                        </div>
                                    </div>
                                    <div class="col-2 text-right">￥{{item.price}}</div>
                                    <div class="col-2 text-right">
                                        <a href="javascript:" class="goods-select" v-bind:index="index">选择</a>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" data-backdrop="static" id="addDateModal" tabindex="-1" role="dialog"
             aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">添加开放日期</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6 mb-2">
                                开始日期
                            </div>
                            <div class="col-6 mb-2">
                                结束日期
                            </div>
                            <div class="col-6 mb-2">
                                <input value="<?= date('Y-m-d') ?>" id="date_timepicker_start" class="form-control">
                            </div>
                            <div class="col-6 mb-2">
                                <input id="date_timepicker_end" class="form-control">
                            </div>
                        </div>
                        <div class="text-muted fs-sm mt-1">只可选择今日起一个月内的时间</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-primary add-date-confirm">确认</button>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>

<script>
    var app = new Vue({
        el: "#app",
        data: {
            goodsList: null,
            goods: null,
            open_time: [],
            open_date: [],
        },
    });

    $(document).on("submit", ".goods-search-form", function () {
        var form = $(this);
        var btn = form.find(".submit-btn");
        btn.btnLoading("正在查找");
        $.ajax({
            url: form.attr("action"),
            type: "get",
            dataType: "json",
            data: form.serialize(),
            success: function (res) {
                btn.btnReset();
                if (res.code == 0) {
                    app.goodsList = res.data.list;
                }
            }
        });
        return false;
    });

    $(document).on("click", ".goods-select", function () {
        var index = $(this).attr("index");
        var goods = app.goodsList[index];
        $("#searchGoodsModal").modal("hide");
        $(".search-goods-name").val(goods.name);
        $(".search-goods-id").val(goods.id);
        for (var i in goods.attr) {
            goods.attr[i].seckill_price = parseFloat(goods.attr[i].price == 0 ? goods.price : goods.attr[i].price);
            goods.attr[i].seckill_num = goods.attr[i].num;
            goods.attr[i].sell_num = 0;
        }
        app.goods = goods;
    });

    function checkGoodsData() {
        if (!app.goods)
            return;
        for (var i in app.goods.attr) {
            if (!app.goods.attr[i].seckill_price)
                app.goods.attr[i].seckill_price = 0;
            if (!app.goods.attr[i].seckill_num)
                app.goods.attr[i].seckill_num = 0;

            app.goods.attr[i].seckill_price = parseFloat(app.goods.attr[i].seckill_price);
            app.goods.attr[i].seckill_num = parseInt(app.goods.attr[i].seckill_num);
            if (app.goods.attr[i].seckill_price < 0.01 || app.goods.attr[i].seckill_price > (app.goods.attr[i].price == 0 ? app.goods.price : app.goods.attr[i].price)) {
                app.goods.attr[i].seckill_price = parseFloat(app.goods.attr[i].price == 0 ? app.goods.price : app.goods.attr[i].price);
            }
            if (app.goods.attr[i].seckill_num < 1 || app.goods.attr[i].seckill_num > app.goods.attr[i].num) {
                app.goods.attr[i].seckill_num = app.goods.attr[i].num;
            }
        }
    }

    $(document).on("change", ".check-goods-data", function () {
        checkGoodsData();
    });

    $(document).on("change", ".open-time-checkbox", function () {
        var val = $(this).val();
        if ($(this).prop("checked")) {
            app.open_time.push(val);
        } else {
            for (var i in app.open_time) {
                if (app.open_time[i] == val) {
                    app.open_time.splice(i, 1);
                }
            }
        }
    });

    $.datetimepicker.setLocale("zh");
    $('#quickAddDate').datetimepicker({
        format: "Y-m-d",
        minDate: "<?=date('Y-m-d')?>",
        maxDate: "<?=date('Y-m-d', strtotime('+1 month'))?>",
        timepicker: false,
        onSelectDate: function (ct, $i) {
            var y = ct.getFullYear();
            var m = ct.getMonth() + 1;
            var d = ct.getDate();
            var date = y + "-" + (m < 10 ? "0" + m : m) + "-" + (d < 10 ? "0" + d : d);
            var in_array = false;
            for (var i in app.open_date) {
                if (app.open_date[i] == date) {
                    in_array = true;
                    break;
                }
            }
            if (!in_array) {
                app.open_date.push(date);
                app.open_date.sort();
            } else {
                $.myToast({title: "日期" + date + "已存在", timeout: 1000});
            }
        },
    });

    $('#date_timepicker_start').datetimepicker({
        format: 'Y-m-d',
        minDate: "<?=date('Y-m-d')?>",
        maxDate: "<?=date('Y-m-d', strtotime('+1 month'))?>",
        onShow: function (ct) {
            this.setOptions({
                maxDate: $('#date_timepicker_end').val() ? $('#date_timepicker_end').val() : "<?=date('Y-m-d', strtotime('+1 month'))?>"
            })
        },
        timepicker: false
    });
    $('#date_timepicker_end').datetimepicker({
        format: 'Y-m-d',
        minDate: "<?=date('Y-m-d')?>",
        maxDate: "<?=date('Y-m-d', strtotime('+1 month'))?>",
        onShow: function (ct) {
            this.setOptions({
                minDate: $('#date_timepicker_start').val() ? $('#date_timepicker_start').val() : false
            })
        },
        timepicker: false
    });


    $(document).on("click", ".add-date-confirm", function () {
        var start_date = $("#date_timepicker_start").val();
        var end_date = $("#date_timepicker_end").val();
        var list = getDateList(start_date, end_date);
        for (var i in list) {
            var date = list[i];
            var in_array = false;
            for (var j in app.open_date) {
                if (app.open_date[j] == date) {
                    in_array = true;
                    break;
                }
            }
            if (!in_array) {
                app.open_date.push(date);
            }
        }
        app.open_date.sort();
        $("#addDateModal").modal("hide");

        function getDateList(start_date_str, end_date_str) {
            start_date_str = start_date_str.split("-");
            end_date_str = end_date_str.split("-");
            if (start_date_str.length != 3 || end_date_str.length != 3)
                return;
            var start_date = new Date(start_date_str[0], start_date_str[1] - 1, start_date_str[2]);
            var end_date = new Date(end_date_str[0], end_date_str[1] - 1, end_date_str[2]);
            var i = 0;
            var list = [];
            while (start_date <= end_date) {
                var y = start_date.getFullYear();
                var m = start_date.getMonth() + 1;
                var d = start_date.getDate();
                var date = y + "-" + (m < 10 ? "0" + m : m) + "-" + (d < 10 ? "0" + d : d);
                list.push(date);
                start_date.setDate(start_date.getDate() + 1);
            }
            return list;
        }
    });

    $(document).on("click", ".open-date-delete", function () {
        var index = $(this).attr("index");
        app.open_date.splice(index, 1);
    });

    $(document).on("click", ".seckill-submit-btn", function () {
        if (!app.goods || app.open_time.length == 0 || app.open_date.length == 0) {
            $.myToast({
                content: "请完善秒杀商品信息",
            });
            return;
        }
        var btn = $(this);
        var form = $(".submit-form");
        btn.btnLoading(btn.text());
        $.ajax({
            type: "post",
            dataType: "json",
            data: {
                _csrf: _csrf,
                goods_id: app.goods.id,
                attr: JSON.stringify(app.goods.attr),
                open_time: JSON.stringify(app.open_time),
                open_date: JSON.stringify(app.open_date),
                buy_max: $('input[name=buy_max]').val(),
            },
            success: function (res) {
                if (res.code == 0) {
                    $.alert({
                        content: res.msg,
                        confirm: function () {
                            location.href = res.data.return_url;
                        }
                    });
                } else {
                    btn.btnReset();
                    $.alert({
                        content: res.msg,
                    });
                }
            }
        });
    });

    $(document).on('change', '.no-buy-max', function () {
        updateBuyMax();
    });

    function updateBuyMax() {
        if ($('.no-buy-max').prop('checked')) {
            $('input[name=buy_max]').val(0).prop('readonly', true);
        } else {
            $('input[name=buy_max]').val('').prop('readonly', false);
        }
    }

    updateBuyMax();
</script>
