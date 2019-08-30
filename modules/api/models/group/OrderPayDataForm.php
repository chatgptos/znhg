<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/18
 * Time: 12:11
 */

namespace app\modules\api\models\group;


use app\models\FormId;
use app\models\Goods;
use app\models\OrderDetail;
use app\models\PtGoods;
use app\models\PtOrder;
use app\models\PtOrderDetail;
use app\models\Setting;
use app\models\User;
use app\modules\api\models\Model;
use yii\helpers\VarDumper;

/**
 * @property User $user
 * @property Order $order
 */
class OrderPayDataForm extends Model
{
    public $store_id;
    public $order_id;
    public $pay_type;
    public $user;

    private $wechat;
    private $order;

    public function rules()
    {
        return [
            [['order_id', 'pay_type',], 'required'],
            [['pay_type'], 'in', 'range' => ['ALIPAY', 'WECHAT_PAY']],
        ];
    }

    public function search()
    {
        $this->wechat = $this->getWechat();
        if (!$this->validate())
            return $this->getModelError();
        $this->order = PtOrder::findOne([
            'store_id' => $this->store_id,
            'id' => $this->order_id,
        ]);
        if (!$this->order)
            return [
                'code' => 1,
                'msg' => '订单不存在',
            ];

        $goods_names = '';
        $goods_list = PtOrderDetail::find()->alias('od')->leftJoin(['g' => PtGoods::tableName()], 'g.id=od.goods_id')->where([
            'od.order_id' => $this->order->id,
            'od.is_delete' => 0,
        ])->select('g.name')->asArray()->all();
        foreach ($goods_list as $goods)
            $goods_names .= $goods['name'] . ';';
        $goods_names = mb_substr($goods_names, 0, 32, 'utf-8');
        if ($this->pay_type == 'WECHAT_PAY') {
            $res = $this->unifiedOrder($goods_names);
            if (isset($res['code']) && $res['code'] == 1) {
                return $res;
            }

            //记录prepay_id发送模板消息用到
            FormId::addFormId([
                'store_id' => $this->store_id,
                'user_id' => $this->user->id,
                'wechat_open_id' => $this->user->wechat_open_id,
                'form_id' => $res['prepay_id'],
                'type' => 'prepay_id',
                'order_no' => $this->order->order_no,
            ]);

            $pay_data = [
                'appId' => $this->wechat->appId,
                'timeStamp' => '' . time(),
                'nonceStr' => md5(uniqid()),
                'package' => 'prepay_id=' . $res['prepay_id'],
                'signType' => 'MD5',
            ];
            $pay_data['paySign'] = $this->wechat->pay->makeSign($pay_data);
            return [
                'code' => 0,
                'msg' => 'success',
                'data' => (object)$pay_data,
                'res' => $res,
                'body' => $goods_names,
            ];
        }
    }

    private function unifiedOrder($goods_names)
    {
        $res = $this->wechat->pay->unifiedOrder([
            'body' => $goods_names,
            'out_trade_no' => $this->order->order_no,
            'total_fee' => $this->order->pay_price * 100,
            'notify_url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/pay-notify.php',
            'trade_type' => 'JSAPI',
            'openid' => $this->user->wechat_open_id,
        ]);
        if (!$res)
            return [
                'code' => 1,
                'msg' => '支付失败',
            ];
        if ($res['return_code'] != 'SUCCESS') {
            return [
                'code' => 1,
                'msg' => '支付失败，' . (isset($res['return_msg']) ? $res['return_msg'] : ''),
                'res' => $res,
            ];
        }
        if ($res['result_code'] != 'SUCCESS') {
            if ($res['err_code'] == 'INVALID_REQUEST') {//商户订单号重复
                $this->order->order_no = (new OrderSubmitForm())->getOrderNo();
                $this->order->save();
                return $this->unifiedOrder($goods_names);
            } else {
                return [
                    'code' => 1,
                    'msg' => '支付失败，' . (isset($res['err_code_des']) ? $res['err_code_des'] : ''),
                    'res' => $res,
                ];
            }
        }
        return $res;
    }
}