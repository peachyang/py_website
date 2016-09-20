<?php

namespace Seahinet\Retailer\ViewModel;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Retailer\Model\StoreTemplate;
use Seahinet\Retailer\Model\Collection\StoreTemplateCollection;
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
				$template = new StoreTemplate();
				
				$templateView = $template->load($id);
			}
		}else{
				$template = new StoreTemplateCollection();
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
	   		'product_recommend');
			
	   foreach ($tempParams as $value) {
	   		$params = $this->divideParam($value,$final_view);
			foreach ($params as $key1 => $value1) {		
				$func = 'template_'.$value;
				$final_view = str_replace('{{'.$value.':'.$value1.'}}', $this->$func($value1), $final_view);
			}
	   }

       return $final_view;
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
		$template = new StoreTemplateCollection();
		if($judge==0)
			$template->storeTemplateList($segment->get('customer')['store_id']);
		else
			$template->storeTemplateList(0);
		return $template;
		
	}
	
	/*
	 * template content 
	 * 
	 * 
	*/
	
	public function template_long_search($params=''){
		$content = '<label class="search-label">本店搜索</label>
                <input class="keyword" type="text" name="keyword" value="" />&nbsp;&nbsp;
                <input class="price-from" type="text" name="price-from" value="" />
                &nbsp;-&nbsp;
                <input class="price-to" type="text" name="price-to" value="" />
                <button class="search-button">搜索</button>';
		return $content;
	}
	
	public function template_short_search($params=''){
		$content = '<div class="title"><h2>本店搜索</h2></div>
                <div class="search-table">
                    <table>
                        <tr>
                            <td class="label">关键字:</td>
                            <td><input class="keyword" type="text" name="key" value="" /></td>
                        </tr>
                        <tr>
                            <td class="label">价格:</td>
                            <td><input class="price-from" type="text" name="pri-from" value="" />&nbsp;&nbsp;<input class="price-to" type="text" name="pri-to" value="" />
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
		
		$content = '<ul>';
       	for($i=0;$i<4;$i++)
		{
			$content .= '<li class="col-md-3">
                            <div>
                                <a href=""><img class="pic" src="'.$this->getBaseUrl('/pub/theme/default/frontend/images/sample.jpg').'"  /></a>
                                <p class="price"><span class="actural">￥119.00 </span><span class="discount">￥119.00</span></p>
                                <h3 class="product-name"><a href="">'.(empty($params)? "" :$params['hot_text']).'雄鹰能量棒Eagle Energy吸入式咖啡因能量棒提神醒脑的口袋咖啡</a></h3>
                                <p class="paid-count">1999人付款</p>
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

    
}
    