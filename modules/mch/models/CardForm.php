<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/27
 * Time: 15:25
 */

namespace app\modules\mch\models;

/**
 * @property \app\models\Card $card;
 */
class CardForm extends Model
{
    public $store_id;
    public $card;

    public $name;
    public $pic_url;
    public $content;

    public function rules()
    {
        return [
            [['name','pic_url','content'],'trim'],
            [['name','pic_url','content'],'string'],
            [['name','pic_url','content'],'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name'=>'卡券名称',
            'pic_url'=>'卡券图片',
            'content'=>'卡券描述'
        ];
    }

    public function save()
    {
        if(!$this->validate()){
            $this->getModelError();
        }

        if($this->card->isNewRecord){
            $this->card->is_delete = 0;
            $this->card->store_id = $this->store_id;
            $this->card->addtime = time();
        }

        $this->card->name = $this->name;
        $this->card->pic_url = $this->pic_url;
        $this->card->content = $this->content;

        if($this->card->save()){
            return [
                'code'=>0,
                'msg'=>'成功'
            ];
        }else{
            return $this->getModelError($this->card);
        }
    }
}