<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/21
 * Time: 14:55
 */

namespace app\modules\mch\extensions;


use app\models\OrderForm;
use yii\helpers\VarDumper;

class Export
{
    //导出  header
    public function exportHeader($EXCEL_OUT)
    {
        header("Content-type:text/csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $EXCEL_OUT;
    }

    //判断是否含有英文逗号，英文引号
    public static function Check($str)
    {
        if (strpos($str, ',')) {
            if (strpos($str, "\"") || strpos($str, "\"") == 0) {
                $str = str_replace("\"", "\"\"", $str);
            }
            $str = "\"" . $str . "\"";
        }
        return $str;
    }

    /**
     * @param $info
     * 导出订单
     */
    public static function order($info)
    {
        $title = "序号,订单号,用户,商品名,商品信息,收件人,电话,地址,总金额（含运费）,运费,实际付款,付款状态,申请状态,发货状态,收货状态,快递单号,快递公司,下单时间";
        $title .= "\n";
        $EXCEL_OUT = iconv('UTF-8', 'GB2312', $title);
        foreach ($info as $index => $value) {
            $out = array();
            $out[] = $index + 1;
            $out[] = trim("\"\t" . $value['order_no'] . "\"");
            $out[] = trim("\"\t" . self::Check($value['nickname']) . "\"");

            $goodsName = "";//商品名
            $goods_str = "";//商品信息
            foreach ($value['goods_list'] as $i => $v) {
                $goodsName .= "商品名：" . $v['name'];
                $attr_list = json_decode($v['attr']);
                if (is_array($attr_list)) {
                    foreach ($attr_list as $attr) {
                        $goods_str .= $attr->attr_group_name . "：" . $attr->attr_name . "，";
                    }
                }
                $goods_str .= "数量：" . $v['num'] . $v['unit'] . "，";
                $goods_str .= "小计：" . $v['total_price'] . "元";
                $goods_str .= "；";
            }
            $out[] = self::Check($goodsName);
            $out[] = self::Check($goods_str);

            $out[] = self::Check($value['name']);
            $out[] = trim("\"\t" . $value['mobile'] . "\"");
            $out[] = self::Check($value['address']);
            $out[] = $value['total_price'] . "元";
            $out[] = $value['express_price'] . "元";
            $out[] = $value['pay_price'] . "元";
            $out[] = ($value['is_pay'] == 1) ? "已付款" : "未付款";
            $out[] = ($value['apply_delete'] == 1) ? "取消中" : "无";
            $out[] = ($value['is_send'] == 1) ? "已发货" : "未发货";
            $out[] = ($value['is_confirm'] == 1) ? "已收货" : "未收货";
            $out[] = self::Check($value['express_no']);
            $out[] = self::Check($value['express']);


            $out[] = trim("\"\t" . date('Y-m-d H:i', $value['addtime']) . "\"");
//            $EXCEL_OUT .= iconv('UTF-8', 'GB2312', implode($out,',')."\n");
            $EXCEL_OUT .= mb_convert_encoding(implode($out, ',') . "\n", 'GB2312', 'UTF-8');//需要先启用 mbstring 扩展库，在 php.ini里将; extension=php_mbstring.dll 前面的 ; 去掉
        }
        $name = "订单导出-" . date('YmdHis', time());//导出文件名称
        header("Content-Disposition:attachment;filename={$name}.csv"); //“生成文件名称”=自定义
        self::exportHeader($EXCEL_OUT);
        exit();
    }

    /**
     * @param $info
     * 导出订单 2.0
     */
    public static function order_2($info)
    {
        $title = "序号,订单号,用户,商品名,规格,数量,收件人,电话,地址,总金额（含运费）,运费,实际付款,付款状态,申请状态,发货状态,收货状态,快递单号,快递公司,下单时间,备注";
        $title .= "\n";
        $EXCEL_OUT = iconv('UTF-8', 'GBK', $title);
        $count = 1;
        foreach ($info as $index => $value) {
            $order_form = OrderForm::findAll(['store_id' => $value['store_id'], 'order_id' => $value['id'], 'is_delete' => 0]);
            foreach ($value['goods_list'] as $i => $v) {
                $price = round($v['total_price'] * $value['pay_price'] / ($value['total_price'] - $value['express_price']), 2);
                $goods_str = "";//规格
                $out = array();
                $out[] = $count;
                $count++;
                $out[] = trim("\"\t" . $value['order_no'] . "\"");
                $out[] = trim("\"\t" . self::Check($value['nickname']) . "\"");
                $out[] = trim("\"\t" . self::Check($v['name']) . "\"");
                $attr_list = json_decode($v['attr']);
                if (is_array($attr_list)) {
                    foreach ($attr_list as $attr) {
                        $goods_str .= $attr->attr_group_name . "：" . $attr->attr_name.',';
                    }
                }
                $out[] = self::Check($goods_str);
                $out[] = $v['num'] . $v['unit'];

                $out[] = self::Check($value['name']);
                $out[] = trim("\"\t" . $value['mobile'] . "\"");
                $out[] = self::Check($value['address']);
                $out[] = $value['total_price'] . "元";
                $out[] = $value['express_price'] . "元";
                $out[] = $price . "元";
                $out[] = ($value['is_pay'] == 1) ? "已付款" : "未付款";
                $out[] = ($value['apply_delete'] == 1) ? "取消中" : "无";
                $out[] = ($value['is_send'] == 1) ? "已发货" : "未发货";
                $out[] = ($value['is_confirm'] == 1) ? "已收货" : "未收货";
                $out[] = self::Check($value['express_no']);
                $out[] = self::Check($value['express']);
                $out[] = trim("\"\t" . date('Y-m-d H:i', $value['addtime']) . "\"");
                if ($order_form) {
                    $str = '';
                    foreach ($order_form as $key => $item) {
                        $str .= $item['key'] . ':' . $item['value'].',';
                    }
                    $content = self::Check($str);
                } else {
                    $content = self::Check($value['content']);
                }
                $out[] = $content;
                $EXCEL_OUT .= mb_convert_encoding(implode($out, ',') . "\n", 'GBK', 'UTF-8');//需要先启用 mbstring 扩展库，在 php.ini里将; extension=php_mbstring.dll 前面的 ; 去掉

            }
//            $EXCEL_OUT .= iconv('UTF-8', 'GB2312', implode($out,',')."\n");
        }
        $name = "订单导出-" . date('YmdHis', time());//导出文件名称
        header("Content-Disposition:attachment;filename={$name}.csv"); //“生成文件名称”=自定义
        self::exportHeader($EXCEL_OUT);
        exit();
    }

    /**
     * @param $info
     * 导出售后订单
     */
    public static function refund($info)
    {
        $title = "序号,订单号,用户,商品信息,收件人,电话,地址,售后类型,退款金额,申请理由,状态,售后申请时间";
        $title .= "\n";
        $EXCEL_OUT = iconv('UTF-8', 'GBK', $title);
        foreach ($info as $index => $value) {
            $out = array();
            $out[] = $index + 1;
            $out[] = trim("\"\t" . $value['order_no'] . "\"");
            $out[] = trim("\"\t" . $value['nickname'] . "\"");

            $goods_str = "";//商品信息
            $goods_str .= "商品名：" . $value['goods_name'];
            $attr_list = json_decode($value['attr']);
            if (is_array($attr_list)) {
                foreach ($attr_list as $attr) {
                    $goods_str .= "，" . $attr->attr_group_name . "：" . $attr->attr_name;
                }
            }
            $goods_str .= "，数量：" . $value['num'] . "件，";
            $goods_str .= "金额：" . $value['total_price'] . "元";
            $out[] = self::Check($goods_str);

            $out[] = self::Check($value['name']);
            $out[] = trim("\"\t" . $value['mobile'] . "\"");
            $out[] = self::Check($value['address']);
            if ($value['refund_type'] == 1) {
                $out[] = "退货退款";
                $out[] = $value['refund_price'] . "元";
                $out[] = self::check($value['refund_desc']);
            } else if ($value['refund_type'] == 2) {
                $out[] = "换货";
                $out[] = $value['refund_price'] . "元";
                $out[] = self::check($value['refund_desc']);
            }

            if ($value['refund_status'] == 0) {
                $out[] = "待处理";
            } else if ($value['refund_status'] == 1) {
                $out[] = "已同意退款退货";
            } else if ($value['refund_status'] == 2) {
                $out[] = "已同意换";
            } else if ($value['refund_status'] == 3) {
                if ($value['refund_type'] == 1) {
                    $str = "已拒绝退货退款";
                } else {
                    $str = "已拒换货";
                }
                $out[] = self::Check($str . "，拒绝理由：" . $value['refund_refuse_desc']);
            }


            $out[] = trim("\"\t" . date('Y-m-d H:i', $value['addtime']) . "\"");
//            $EXCEL_OUT .= iconv('UTF-8', 'GB2312', implode($out,',')."\n");
            $EXCEL_OUT .= mb_convert_encoding(implode($out, ',') . "\n", 'GB2312', 'UTF-8');
        }


        $name = "售后订单导出-" . date('YmdHis', time());//导出文件名称
        header("Content-Disposition:attachment;filename={$name}.csv"); //“生成文件名称”=自定义
        self::exportHeader($EXCEL_OUT);
        exit();
    }
}