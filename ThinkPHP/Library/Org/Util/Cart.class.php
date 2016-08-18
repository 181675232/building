<?php
namespace Org\Util;

//购物车类
class Cart{
	private $table;
	
    public function __construct() {
		if (!$this->table){
			$this->table = M('cart');
		}   
    }
 
    /*
    添加商品
    param int $id 商品主键
          int $num 购物数量
    */
    public  function addcart($id,$uid,$num) {
    	if (!$num){
    		$num = 1;
    	}
        //如果该商品已存在则直接加其数量
        if ($this->table->where("uid = $uid and goods_id = $id")->find()) {
            return $this->table->where("uid = $uid and goods_id = $id")->setInc('num');
        }
        $item['goods_id'] = $id;
        $item['uid'] = $uid;
        $item['num'] = $num;
		$item['addtime'] = time();
        return $this->table->add($item);
    }
 
    /*
    修改购物车中的商品数量
    int $id 购物车id
    int $num 某商品修改后的数量，即直接把某商品
    的数量改为$num
    */
    public function editNum($id,$num) {
    	if (!$num){
    		$num = 1;
    	}
    	$this->table->where("id = $id")->setField("num",$num);
    	return true;
    }
 
    /*
    商品数量+1
    */
    public function incNum($id) {
        if ($this->table->where("id = $id")->setInc('num')){
    		return true;
    	}else {
    		return false;
    	}
    }
 
//     /*
//     商品数量-1
//     */
//     public function decNum($id) {
//         //如果减少后，数量为0，则把这个商品删掉
//     	$num = $this->table->where("id = $id")->getField('num');
//     	if ($num){
//     		return true;
//     	}else {
//     		delItem
//     	}
//     }
 
    /*
    删除商品
    */
    public function delItem($id) {
        if ($this->table->delete($id)){
    		return true;
    	}else {
    		return false;
    	}
    }
     
    /*
    获取单个商品
    */
    public function getItem($id) {
        return $_SESSION['cart'][$id];
    }
 
    /*
    查询购物车中商品的种类
    */
    public function getCnt($uid) {
        return $this->table->where("uid = $uid")->count();
    }
     
    /*
    查询购物车中商品的个数
    */
    public function getNum($uid){
        return $this->table->where("uid = $uid")->sum('num');
    }
 
    /*
    购物车中商品的总金额
    */
//     public function getPrice() {
//         //数量为0，价钱为0
//         if ($this->getCnt() == 0) {
//             return 0;
//         }
//         $price = 0.00;
//         $data = $_SESSION['cart'];
//         foreach ($data as $item) {
//             $price += $item['num'] * $item['price'];
//         }
//         return sprintf("%01.2f", $price);
//     }
 
    /*
    清空购物车
    */
    public function clear($uid) {
    	if ($this->table->where("uid = $uid")->delete()){
    		return true;
    	}else {
    		return false;
    	}
    }
    
    //获取购物车数据
    public function getcart($uid){
    	$data = $this->table->field('t_cart.id,t_cart.goods_id,t_cart.num,t_cart.uid,t_cart.addtime,t_goods.title,t_goods.price,t_goods.prices,coalesce(t_img.simg,g.simg) as simg')
		->join('left join t_goods on t_goods.id = t_cart.goods_id')
		->join('left join t_img on t_img.pid = t_cart.goods_id and t_img.type="goods"')
		->join('left join t_goods as g on g.id = t_goods.pid')
		->group('t_cart.goods_id')
		->where("t_cart.uid = $uid")->order('t_cart.addtime')->select();
		if ($data){
			return $data;
		}else {
			return false;
		}
    }
    
    //选中的购物清单
    public function select_cart($ids){
    	$where['t_cart.id'] = array('in',$ids);
    	$data = $this->table->field('t_cart.id,t_cart.goods_id,t_cart.num,t_cart.uid,t_cart.addtime,t_goods.title,t_goods.price,t_goods.prices,coalesce(t_img.simg,g.simg) as simg')
    	->join('left join t_goods on t_goods.id = t_cart.goods_id')
    	->join('left join t_img on t_img.pid = t_cart.goods_id and t_img.type="goods"')
    	->join('left join t_goods as g on g.id = t_goods.pid')
    	->group('t_cart.goods_id')
    	->where($where)->order('t_cart.addtime')->select();
    	if ($data){
    		return $data;
    	}else {
    		return false;
    	}
    }
    
    
    
    
}