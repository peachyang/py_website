<?php

namespace Seahinet\Retailer\ViewModel;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Retailer\ViewModel\SalesProducts;
use Seahinet\Retailer\Model\StoreTemplate;
use Seahinet\Retailer\Model\Retailer;
use Seahinet\Retailer\Model\Collection\RetailerCollection;
use Seahinet\Retailer\Model\Collection\StoreTemplateCollection;
use Seahinet\Retailer\Model\Collection\StorePicInfoCollection;
use Seahinet\Lib\Session\Segment;
use Zend\Db\Sql\Expression;


class StoreDecoration extends Template
{
    /**  
    * Get store template view
    * 
    * @access public 
    * @return object 
    */ 
    public function getTemplateView($model = 0)
    {
		$templateView = [];
		$id = $this->getQuery('id');
		$segment = new Segment('customer');	
		if(!empty($id)){
			if($id!='-1')
			{		
				$template = new StoreTemplate;
				
				$templateView = $template->load($id);
			}
		}else{
				$template = new StoreTemplateCollection;
				$templateViewCollection = $template->storeTemplateList($segment->get('customer')['store_id'],1);
				if(!empty($templateViewCollection))
					$templateView = $templateViewCollection[0];
				else
					$templateView = [];
		}
			
		
		if(!empty($templateView))
		{
			$templateView['code_model'] = $this->changeModel($templateView['code_model']);
			$templateView['src_model'] = $this->changeModel($templateView['src_model']);
			$templateView['stable_params'] = $this->changeStableParams($templateView['stable_params']);
		}
		
		if( !empty($templateView) && $templateView['store_id'] != $segment->get('customer')['store_id'] && $templateView['store_id']!=0 )
			$templateView = [];
		
		return $templateView;				 		      
    }
	
	public function changeModel($view){
       $final_view = $view;
	   
	   $tempParams = array(
	   		'long_search',
	   		'short_search',
	   		'product_class',
	   		'sales_amount',
	   		'hot_product',
	   		'store_recommend',
	   		'product_recommend',
	   		'pic_carousel');
			
	   foreach ($tempParams as $value) {
	   		$params = $this->divideParam($value,$final_view);
			foreach ($params as $key1 => $value1) {		
				$func = 'template_'.$value;
				$final_view = str_replace('{{'.$value.':'.$value1.'}}', $this->$func($value1), $final_view);
			}
	   }

       return $final_view;
	}
	
	public function changeStableParams($params){
		$params = urldecode($params);
		$params = json_decode($params,true);
		return $params;		
	}
	
	public function divideParam($value,$str){
	
		preg_match_all("|{{".$value.":([^^]*?)}}|u", $str, $matches);
		return $matches[1];
	}
	
	/**  
    * Get store template list
    * 
    * @access public 
    * @return object 
	* @param  judge 1:theme template; 0:customer template
    */ 
	public function getTemplateList($judge=0){
		$segment = new Segment('customer');
		$template = new StoreTemplateCollection;
		if($judge==0)
			$template->storeTemplateList($segment->get('customer')['store_id']);
		else
			$template->storeTemplateList(0);
		return $template;
		
	}
	
	public function getStorePicInfo($code){
		$segment = new Segment('customer');
		$Scollection = new StorePicInfoCollection;
		$Scollection->where(['resource_category_code'=>$code,'store_decoration_picinfo.store_id'=>$segment->get('customer')['store_id']])
		->join('resource','store_decoration_picinfo.resource_id = resource.id',['real_name'],'left')->order(['resource.created_at'=>'DESC']);
	
		return $Scollection;
		
	}
	
	public function getStoreBanner(){
		$segment = new Segment('customer');		
		$retailer = new RetailerCollection;
		$retailer->where(['retailer.customer_id'=>$segment->get('customer')['id'],'retailer.store_id'=>$segment->get('customer')['store_id']])
		->join('resource','retailer.photo = resource.id',['real_name'],'left')->order(['resource.created_at'=>'DESC']);
		//return $segment->get('customer');
		return empty($retailer) ? [] : $retailer[0] ;
	}
	
	public function getSearchProducts(){
		$condition = $this->getQuery();
		$products = new SalesProducts();
		$productsData = $products->getRetailerSalesProducts($condition);
		$content = "";
		foreach ($productsData as $key => $value) {
			$thumbnail = $products->getProduct($value['id'])->getThumbnail();
			if (strpos($thumbnail, 'http') === false) {
				$picURL = $this->getBaseUrl('pub/resource/image/resized/' . $thumbnail); 	
			}else {
				$picURL = $thumbnail;
			}
			
			$content .= '<li class="col-md-3" >
                            <div>
                                <a href="javascript:void(0)"><img class="pic" src="'.$picURL.'"  /></a>
                                <p class="price"><span class="actural">'.$products->getCurrency()->format($value['price']).' </span><span class="discount">'.$products->getCurrency()->format($value['price']).'</span></p>
                                <h3 class="product-name"><a href="">'.$value['name'].'</a></h3>
                                <p class="paid-count"></p>
                            </div>
             			</li>';
		}
		return $content;                
	}
	
	/*
	 * template content 
	 * 
	 * 
	*/
	public function template_logo_top($params=''){
		if(!empty($params))
		{
//			if($params['widthSetType'] == "1")
//				$width = $params['widthSet']."px";
//			else
//				$width = '100%';
			$height = $params['heightSet']."px";
				
		}else{
			$width = '1128px';
			$height = '200px';
		}
		$retailer = $this->getStoreBanner();
//		if(!empty($retailer['real_name']))
//			$content = '<img style="width:'.$width.';height:'.$height.'" src="'.$this->getBaseUrl('/pub/resource/image/'.$retailer['real_name']).'">';
//		else
//			$content = '<img style="width:'.$width.';height:'.$height.'" src="'.$this->getBaseUrl('/pub/theme/default/frontend/dragResource/images/text1.jpg').'">';
		if(!empty($retailer['real_name']))
			$content = $this->getBaseUrl('/pub/resource/image/'.$retailer['real_name']);
		else
			$content = $this->getBaseUrl('/pub/theme/default/frontend/dragResource/images/text1.jpg');
		return $content;
	}
	
	
	public function template_menu($params=''){
		$result = $this->getStorePicInfo('menu');
		$content = "";
		foreach ($result as $key => $value) {
			if(trim($value['url'])!="")
				$content .= '<li class="menu" ><a target=_blank href="'.$value['url'].'">'.$value['pic_title'].'</a></li>';
			else
				$content .= '<li class="menu" ><a  href="javascript:void(0)">'.$value['pic_title'].'</a></li>';
		}
		return $content;
	}
	
	
	public function template_paragraph($params=''){
		$content = '<p> <br><br>可以在此模块中通过编辑器自由输入文字以及编排格式<br><br> </p>';
		return $content;
	
	}
	
	public function template_long_search($params=''){
		$content = '<form target=_blank action="'.$this->getBaseUrl('/retailer/store/viewSearch').'"><label class="search-label">本店搜索</label>
                 <input class="keyword" type="text" name="name" value="" />&nbsp;&nbsp;
                <input class="price-from" type="text" name="price_from" value="" />
                &nbsp;-&nbsp;
                <input class="price-to" type="text" name="price_to" value="" />
                <button class="search-button">搜索</button>
                </form>';
		return $content;
	}
	
	public function template_short_search($params=''){
		$content = '<div class="title"><h2>本店搜索</h2></div>
                <div class="search-table">
                    <form target=_blank action="'.$this->getBaseUrl('/retailer/store/viewSearch').'">
                    <table>
                        <tr>
                            <td class="label">关键字:</td>
                            <td><input class="keyword" type="text" name="name" value="" /></td>
                        </tr>
                        <tr>
                            <td class="label">价格:</td>
                            <td><input class="price-from" type="text" name="price_from" value="" />&nbsp;&nbsp;<input class="price-to" type="text" name="price_to" value="" />
                </td>
                        </tr>
                        <tr>
                            <td class="label">&nbsp;</td>
                            <td><button class="search-button">搜索</button></td>
                        </tr>
                        <tr>
                            <td class="label">热门</td>
                            <td><a href="">雄鹰能量棒</a>&nbsp;<a href="">吸入式咖啡</a></td>
                        </tr>
                    </table>
                    </form>
                </div>';
		return $content;
	}

	public function template_product_class($params=''){
		$content = '<ul class="category_list">
                        <li><a href="">查看所有分类&gt;&gt;</a></li>
                        <li><a href="">按销量</a>&nbsp;<a href="">按价格</a>&nbsp;<a href="">按评价</a>&nbsp;<a href="">按新品</a></li>
                        <li class="dropdown">
                            <span>薄荷糖口香糖补充剂等</span>
                            <ul>
                                <li></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <span>益智开脑洞</span>
                        </li>
                        <li class="dropdown">
                            <span>睡眠放松</span>
                        </li>
                        <li class="dropdown">
                            <span>改善心情</span>
                        </li>
                        <li class="dropdown">
                            <span>能量补充</span>
                        </li>
                    </ul>';
		return $content;
	}
	
	public function template_sales_amount($params=''){
		$content = '<ul>
                        <li>
                            <div class="col-md-4">
                                <a href=""><img class="pic" src="'.$this->getBaseUrl('/pub/theme/default/frontend/images/sample.jpg').'"  /></a>
                                </div>
                                <div class="col-md-8">
                                    <div class="pad-5">
                                        <h3 class="product-name"><a href="">雄鹰能量棒Eagle Energy吸入式咖啡因能量棒提神醒脑的口袋咖啡</a></h3>
                                        <p class="price"><span class="actural">￥119.00 </span></p>
                                        <p class="paid-count">热销<span class="sales-num">900</span>笔</p>
                                    </div>
                            </div>
                        </li>
                    </ul>';
		return $content;
	}

	public function template_hot_product($params=''){
		
		if(!empty($params))
		{	
			$params = urldecode($params);
			$params = json_decode($params,true);
		}
		
		
		$hot_text = empty($params) ? "" : $params['hot_text'];
		$price_from = empty($params) ? "" : $params['price_from'];
		$price_to = empty($params) ? "" : $params['price_to'];
		$select_row = empty($params) ? 1 : $params['select_row'];
		$select_column = empty($params) ? 4 : (int)$params['select_column'];
		$select_col_md = 'col-md-3';
		switch ($select_column) {
			case 3:
				$select_col_md = 'col-md-4';
				break;
			case 2:
				$select_col_md = 'col-md-6';
				break;			
			default:
				$select_col_md = 'col-md-3';
				break;
		}
		
		$condition['name'] = $hot_text;
		$condition['limit'] = $select_column*$select_row;
		$condition['price_from'] = $price_from;
		$condition['price_to'] = $price_to;
		$products = new SalesProducts();
		$productsData = $products->getRetailerSalesProducts($condition);
		
		
		$content = '<ul>';
       	foreach ($productsData as $key => $value) {
			$thumbnail = $products->getProduct($value['id'])->getThumbnail();
			if (strpos($thumbnail, 'http') === false) {
				$picURL = $this->getBaseUrl('pub/resource/image/resized/' . $thumbnail); 	
			}else {
				$picURL = $thumbnail;
			}
			
			$content .= '<li class="'.$select_col_md.'">
                            <div>
                                <a href="javascript:void(0)"><img class="pic" src="'.$picURL.'"  /></a>
                                <p class="price"><span class="actural">'.$products->getCurrency()->format($value['price']).' </span><span class="discount">'.$products->getCurrency()->format($value['price']).'</span></p>
                                <h3 class="product-name"><a href="">'.$value['name'].'</a></h3>
                                <p class="paid-count"></p>
                            </div>
                        </li>';
		}                
        $content .= '</ul>';
		return $content;
	}

	public function template_store_recommend($params=''){
		$content ='';
		for($i=0;$i<3;$i++)
		{
			$content .= '<div class="col-md-4">';
			
			if($i==1)
			{
				$content .= '<div class="prompt-big">
                                <a href=""><img class="pic" src="'.$this->getBaseUrl('/pub/theme/default/frontend/images/sample.jpg').'"  /></a>
                                <p class="price"><span class="actural">￥119.00 </span><span class="discount">￥119.00</span></p>
                                <h3 class="product-name"><a href="">雄鹰能量棒Eagle Energy吸入式咖啡因能量棒提神醒脑的口袋咖啡</a></h3>
                                <p class="paid-count">1999人付款</p>
                            </div>';	
			}else{
				for($j=0;$j<4;$j++){
					$content .= '<div class="col-md-6 col-md-61">
                        <div class="prompt-small"><img class="pic" src="'.$this->getBaseUrl('/pub/theme/default/frontend/images/sample.jpg').'"  /></div>
                    </div>';
				}
			}
			
			
			$content .= '</div>';
		}                

		return $content;
		
	}

	public function template_product_recommend($params=''){
		$content = '<ul>';       	
       	for($i=0;$i<4;$i++)
		{
			$content .= '<li class="col-md-3">
                            <div>
                                <a href=""><img class="pic" src="'.$this->getBaseUrl('/pub/theme/default/frontend/images/sample.jpg').'"  /></a>
                                <p class="price"><span class="actural">￥119.00 </span><span class="paid-count">1999人付款</span></p>
                                <h3 class="product-name"><a href="">雄鹰能量棒Eagle Energy吸入式咖啡因能量棒提神醒脑的口袋咖啡</a></h3>
                            </div>
                        </li>';
		}                
        $content .= '</ul>';		
		return $content;
		
	}
	
	
	public function template_pic_carousel($params=''){
		$content = '<div class="carousel_wrap"><ul class="hiSlider hiSlider3">';
		$result = $this->getStorePicInfo('store_carousel');
       	foreach ($result as $key => $value)
		{	
			if(trim($value['url'])!="")
				$content .= '<li class="hiSlider-item"><a href="'.$value['url'].'" target=_blank ><img src="'.$this->getBaseUrl('/pub/resource/image/'.$value['real_name']).'" alt="'.$value['pic_title'].'"></a></li>';
			else
				$content .= '<li class="hiSlider-item"><img src="'.$this->getBaseUrl('/pub/resource/image/'.$value['real_name']).'" alt="'.$value['pic_title'].'"></li>';
		}                
        $content .= '</ul></div>';
		return $content;
		
	}

    
}
    