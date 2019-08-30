<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/27
 * Time: 9:32
 */

namespace app\modules\api\models\group;


use app\models\Article;
use app\models\Order;
use app\models\PtGoods;
use app\models\PtGoodsPic;
use app\models\PtOrder;
use app\models\PtOrderComment;
use app\models\PtOrderDetail;
use app\models\User;
use app\modules\api\models\Model;
use yii\data\Pagination;

class PtGoodsForm extends Model
{
    public $page = 0;
    public $store_id;

    public $user_id;

    public $gid;

    public $limit;


    /**
     * @return array
     * 拼团商品列表
     */
    public function getList()
    {
        $page = \Yii::$app->request->get('page') ?: 1;
        $limit = (int)\Yii::$app->request->get('limit') ?: 10;
        $cid = \Yii::$app->request->get('cid');
        $query = PtGoods::find()
            ->andWhere(['is_delete' => 0, 'store_id' => $this->store_id, 'status' => 1])
            ->andWhere(['or', ['>', 'limit_time', time()], ['limit_time' => 0]]);
        if ((int)$cid) {
            // 分类
            $query->andWhere(['cat_id' => $cid]);
        } else {
            // 热销
            $query->andWhere(['is_hot' => 1]);
        }
        $count = $query->count();
        $p = new Pagination(['totalCount' => $count, 'pageSize' => $limit, 'page' => $page - 1]);
        $list = $query
            ->offset($p->offset)
            ->limit($p->limit)
            ->orderBy('sort ASC')
            ->asArray()
            ->all();
        foreach ($list AS $key => $goods) {
            $list[$key]['groupList'] = PtOrderDetail::find()
                ->select([
                    'u.avatar_url'
                ])
                ->alias('od')
                ->andWhere(['od.goods_id' => $goods['id']])
                ->leftJoin(['o' => PtOrder::tableName()], 'o.id=od.order_id')
                ->andWhere(['o.is_delete' => 0, 'o.is_pay' => 1, 'o.is_group' => 1, 'o.parent_id' => 0, 'o.is_success' => 0])
                ->andWhere(['>', 'o.limit_time', time()])
                ->leftJoin(['u' => User::tableName()], 'o.user_id=u.id')
                ->andWhere(['!=', 'o.user_id', $this->user_id])
                ->orderBy(['o.addtime' => 'DESC'])
                ->limit(3)
                ->asArray()
                ->all();
            $ptGoodsFind = PtGoods::find()->where(['id' => $goods['id']])->one();
            $list[$key]['virtual_sales'] += $ptGoodsFind->getSalesVolume();
        }


        return [
            'row_count' => intval($count),
            'page_count' => intval($p->pageCount),
            'page' => intval($page),
            'list' => $list,
        ];
    }

    /**
     * @return mixed|string
     * 拼团商品详情
     */
    public function getInfo()
    {
        $info = PtGoods::find()
            ->andWhere(['is_delete' => 0, 'store_id' => $this->store_id, 'status' => 1, 'id' => $this->gid])
            ->andWhere(['or', ['>', 'limit_time', time()], ['limit_time' => 0]])
            ->asArray()
            ->one();
        $goods = PtGoods::find()
            ->andWhere(['or', ['>', 'limit_time', time()], ['limit_time' => 0]])
            ->andWhere(['is_delete' => 0, 'store_id' => $this->store_id, 'status' => 1, 'id' => $this->gid])->one();
        if (!$info) {
            return [
                'code' => 1,
                'msg' => '商品不存在或已下架',
            ];
        }
        $info['pic_list'] = PtGoodsPic::find()
            ->select('pic_url')
            ->andWhere(['goods_id' => $this->gid, 'is_delete' => 0])
            ->column();

        $info['attr'] = json_decode($info['attr'], true);
        $info['service'] = explode(',', $info['service']);
        $attr_group_list = $goods->getAttrGroupList();

        $groupList = PtOrderDetail::find()
            ->select([
                'u.avatar_url', 'u.nickname',
                'o.limit_time', 'o.id',
            ])
            ->alias('od')
            ->andWhere(['od.goods_id' => $goods->id])
            ->leftJoin(['o' => PtOrder::tableName()], 'o.id=od.order_id')
            ->andWhere(['o.is_delete' => 0, 'o.is_pay' => 1, 'o.is_group' => 1, 'o.parent_id' => 0, 'o.is_success' => 0])
            ->andWhere(['>', 'o.limit_time', time()])
            ->leftJoin(['u' => User::tableName()], 'o.user_id=u.id')
            ->andWhere(['!=', 'o.user_id', $this->user_id])
            ->orderBy(['o.addtime' => 'DESC'])
            ->limit(3)
            ->asArray()
            ->all();
        foreach ($groupList AS $key => $val) {
            $limit_time1 = [
                'days' => '00',
                'hours' => '00',
                'mins' => '00',
                'secs' => '00',
            ];

            $order = PtOrder::findOne($val['id']);
            $groupList[$key]['surplus'] = $order->getSurplusGruop();

            $timediff1 = $order->limit_time - time();
            $limit_time1['days'] = intval($timediff1 / 86400);
            //计算小时数
            $remain1 = $timediff1 % 86400;
            $limit_time1['hours'] = intval($remain1 / 3600);
            //计算分钟数
            $remain1 = $remain1 % 3600;
            $limit_time1['mins'] = intval($remain1 / 60);
            //计算秒数
            $limit_time1['secs'] = $remain1 % 60;

            $groupList[$key]['limit_time'] = $limit_time1;
            $groupList[$key]['limit_time_ms'] = explode('-', date('Y-m-d-H-i-s', $order->limit_time));

        }

        $limit_time = [
            'days' => '00',
            'hours' => '00',
            'mins' => '00',
            'secs' => '00',
        ];

        if (!empty($goods->limit_time)) {
            $timediff = $goods->limit_time - time();
            if ($timediff < 0) {
                return [
                    'code' => 1,
                    'msg' => '该商品拼团活动已经结束',
                ];
            }

            // 计算天
            $limit_time['days'] = intval($timediff / 86400);

            // 计算小时数
            $remain = $timediff % 86400;
            $limit_time['hours'] = intval($remain / 3600);

            // 计算分钟数
            $remain = $remain % 3600;
            $limit_time['mins'] = intval($remain / 60);

            // 计算秒数
            $limit_time['secs'] = $remain % 60;
        }

        $info['limit_time_ms'] = explode('-', date('Y-m-d-H-i-s', $info['limit_time']));

        // 销量
        $info['virtual_sales'] += $goods->getSalesVolume();

        // 获取拼团规则id
        $groupRuleId = Article::find()->andWhere([
            'store_id' => $this->store_id,
            'article_cat_id' => 3,
        ])->scalar();

        $comment = PtOrderComment::find()
            ->alias('c')
            ->select([
                'c.score', 'c.content', 'c.pic_list', 'c.addtime',
                'u.nickname', 'u.avatar_url',
                'od.attr'
            ])
            ->andWhere(['c.store_id' => $this->store_id, 'c.goods_id' => $info['id'], 'c.is_delete' => 0, 'c.is_hide' => 0])
            ->leftJoin(['u' => User::tableName()], 'u.id = c.user_id')
            ->leftJoin(['od' => PtOrderDetail::tableName()], 'od.id=c.order_detail_id')
            ->orderBy('c.addtime DESC')
            ->limit(2)
            ->asArray()
            ->all();

        foreach ($comment AS $k => $v) {
            $comment[$k]['attr'] = json_decode($v['attr'], true);
            $comment[$k]['pic_list'] = json_decode($v['pic_list'], true);
            $comment[$k]['addtime'] = date('m月d日', $v['addtime']);
            $comment[$k]['nickname'] = $this->substr_cut($v['nickname']);
        }
        if (empty($comment)) {
            $comment = false;
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'info' => $info,
                'attr_group_list' => $attr_group_list,
                'limit_time' => $limit_time,
                'groupList' => $groupList,
                'groupRuleId' => $groupRuleId,
                'comment' => $comment,
                'commentNum' => PtOrderComment::getCount($info['id'], $this->store_id),
            ],
        ];
    }

    /**
     * @return array
     * 评论列表
     */
    public function comment()
    {
        $query = PtOrderComment::find()
            ->alias('c')
            ->select([
                'c.score', 'c.content', 'c.pic_list', 'c.addtime',
                'u.nickname', 'u.avatar_url',
                'od.attr'
            ])
            ->andWhere(['c.store_id' => $this->store_id, 'c.goods_id' => $this->gid, 'c.is_delete' => 0, 'c.is_hide' => 0])
            ->leftJoin(['u' => User::tableName()], 'u.id = c.user_id')
            ->leftJoin(['od' => PtOrderDetail::tableName()], 'od.id=c.order_detail_id');
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page, 'pageSize' => 20]);

        $comment = $query
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->orderBy('c.addtime DESC')
            ->asArray()
            ->all();
        foreach ($comment AS $k => $v) {
            $comment[$k]['attr'] = json_decode($v['attr'], true);
            $comment[$k]['pic_list'] = json_decode($v['pic_list'], true);
            $comment[$k]['addtime'] = date('m月d日', $v['addtime']);
            $comment[$k]['nickname'] = $this->substr_cut($v['nickname']);
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'comment' => $comment,
            ],
        ];
    }

    // 将用户名 做隐藏
    private function substr_cut($user_name)
    {
        $strlen = mb_strlen($user_name, 'utf-8');
        $firstStr = mb_substr($user_name, 0, 1, 'utf-8');
        $lastStr = mb_substr($user_name, -1, 1, 'utf-8');
        return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
    }

}