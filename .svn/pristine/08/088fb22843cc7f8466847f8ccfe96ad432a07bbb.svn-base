<?php
/**
 * Created by PhpStorm.
 * Author: RFly
 * Date: 2/4/15
 * Time: 4:20 PM
 */

namespace App\Controller;

use Think\Controller;
use Think\Model;


class ShopController extends AppController
{
    protected $shopModel;

    public function _initialize()
    {
        $this->shopModel = D('Shop/Shop');
    }

    //商品列表
    public function listGoods($page = 1, $r = 20)
    {
        $this->requireLogin();

        $map['status'] = array('egt', 0);
        $goodsList = $this->shopModel->where($map)->order('createtime desc')->page($page, $r)->select();
        foreach($goodsList as &$v) {
            $v['goods_ico'] = getThumbImageById($v['goods_ico'],200);
        }
        $totalCount = $this->shopModel->where($map)->count();

        $this->apiSuccess("获取商品列表成功", null, array('goodsList' => $goodsList, 'total_count' => $totalCount));
    }

    //兑换商品记录
    public function myGoods($page = 1, $status = 0)
    {
        $this->requireLogin();

        $map['status'] = $status;
        $map['uid'] = $this->getUid();
        $goods_buy_list = D('shop_buy')->where($map)->page($page, 16)->order('createtime desc')->select();
        $totalCount = D('shop_buy')->where($map)->count();
        foreach ($goods_buy_list as &$v) {
            $v['goods'] = D('shop')->where('id=' . $v['goods_id'])->field($this->goods_info)->find();
            $v['category'] = D('shopCategory')->field('id,title')->find($v['goods']['category_id']);
            $v['goods']['goods_ico'] = getThumbImageById($v['goods']['goods_ico'],200);
        }
        $this->apiSuccess("获取已兑换列表成功", null, array('goodsList' => $goods_buy_list, 'total_count' => $totalCount));
    }

    //兑换商品
    public function goodsBuy($id = 0, $name = '', $address = '', $zipcode = '', $phone = '', $address_id = '')
    {
        $this->requireLogin();

        $address = op_t($address);
        $address_id = intval($address_id);
        $num = 1;

        $goods = D('shop')->where('id=' . $id)->find();
        if ($goods) {
            //验证开始
            //判断商品余量
            if ($num > $goods['goods_num']) {
                $this->apiError(-100,'商品余量不足');
            }

            //扣tox_money
            $tox_money_need = $num * $goods['tox_money_need'];
            $my_tox_money = getMyToxMoney();
            if ($tox_money_need > $my_tox_money) {
                $this->apiError(-101,'你的' . getToxMoneyName() . '不足');
            }

            //用户地址
            $shop_address['phone'] = $phone;
            $shop_address['name'] = $name;
            $shop_address['address'] = $address;
            $shop_address['zipcode'] = $zipcode;
            if ($address_id) {
                $address_save = D('shop_address')->where(array('id' => $address_id))->save($shop_address);
                if ($address_save) {
                    D('shop_address')->where(array('id' => $address_id))->setField('change_time', time());
                }
                $data['address_id'] = $address_id;
            } else {
                $shop_address['uid'] = is_login();
                $shop_address['create_time'] = time();
                $data['address_id'] = D('shop_address')->add($shop_address);
            }
            //验证结束

            $data['goods_id'] = $id;
            $data['goods_num'] = $num;
            $data['status'] = 0;
            $data['uid'] = is_login();
            $data['createtime'] = time();


            D('member')->where('uid=' . is_login())->setDec('tox_money', $tox_money_need);
            $res = D('shop_buy')->add($data);
            if ($res) {
                //商品数量减少,已售量增加
                D('shop')->where('id=' . $id)->setDec('goods_num', $num);
                D('shop')->where('id=' . $id)->setInc('sell_num', $num);
                //发送系统消息
                $message = $goods['goods_name'] . "购买成功，请等待发货。";
                D('Message')->sendMessageWithoutCheckSelf(is_login(), $message, '购买成功通知', U('Shop/Index/myGoods', array('status' => '0')));

                //商城记录
                $shop_log['message'] = '用户[' . is_login() . ']' . query_user('nickname', is_login()) . '在' . time_format($data['createtime']) . '购买了商品<a href="index.php?s=/Shop/Index/goodsDetail/id/' . $goods['id'] . '.html" target="_black">' . $goods['goods_name'] . '</a>';
                $shop_log['uid'] = is_login();
                $shop_log['create_time'] = $data['createtime'];
                D('shop_log')->add($shop_log);

                $this->apiSuccess('购买成功！花费了' . $tox_money_need . getToxMoneyName());
            } else {
                $this->apiError(-102,'购买失败！');
            }
        } else {
            $this->apiError(-103,'请选择要购买的商品');
        }
    }
}