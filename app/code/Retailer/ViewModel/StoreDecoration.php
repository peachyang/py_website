<?php

namespace Seahinet\Retailer\ViewModel;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Retailer\ViewModel\SalesProducts;
use Seahinet\Catalog\Model\Product;
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
    public function judge_store_id($current_store_id = null)
    {
        if (empty($current_store_id)) {
            $segment = new Segment('customer');
            $r = new Retailer;
            $r->load($segment->get('customer')->getId(), 'customer_id');
            $store_id = $r['store_id'];
        } else {
            $store_id = $current_store_id;
        }
        return $store_id;
    }

    public function getTemplateView($current_store_id = null, $model = 0)
    {
        $templateView = [];
        $id = $this->getQuery('id');
        $key = $this->getQuery('key');
        $store_id = $this->judge_store_id($current_store_id);

        if ($model != 0)
            $id = $model;
        if (!empty($id)) {
            if ($id != '-1') {
                $template = new StoreTemplate;

                $templateView = $template->load($id);
            }
        } else {
            if (empty($key)) {
                $template = new StoreTemplateCollection;

                $templateViewCollection = $template->storeTemplateList($store_id, 1);
                if (!empty($templateViewCollection))
                    $templateView = $templateViewCollection[0];
                else
                    $templateView = [];
            }else {

                $key = base64_decode($key);
                $storeTemplate = new storeTemplate;
                $templateView = $storeTemplate->load($key);
            }
        }


        if (!empty($templateView)) {
            $templateView['code_model'] = $this->changeModel($templateView['code_model'], $store_id);
            $templateView['src_model'] = $this->changeModel($templateView['src_model'], $store_id);
            $templateView['stable_params'] = $this->changeStableParams($templateView['stable_params'], $store_id);
        }

        if (!empty($templateView) && $templateView['store_id'] != $store_id && $templateView['store_id'] != 0 && is_null($current_store_id))
            $templateView = [];

        return $templateView;
    }

    public function getProductDetailTemplateView($store_id)
    {
        $template = new StoreTemplateCollection;
        $template->storeTemplateList($store_id, 1);
        $template_id = count($template) > 0 ? $template[0]['id'] : -2;
        $view = '';
        if ($template_id > 0) {
            $template = new StoreTemplateCollection;
            $template->storeCustomizeTemplate($store_id, $template_id, 2);
            $final_id = count($template) > 0 ? $template[0]['id'] : -2;
            $view = $this->getTemplateView($store_id, $final_id);
        }
        return $view;
    }

    public function changeModel($view, $current_store_id = null)
    {
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
            $params = $this->divideParam($value, $final_view);
            foreach ($params as $key1 => $value1) {
                $func = 'template_' . $value;
                $final_view = str_replace('{{' . $value . ':' . $value1 . '}}', $this->$func($value1, $current_store_id), $final_view);
            }
        }

        return $final_view;
    }

    public function changeStableParams($params, $current_store_id = null)
    {
        $params = urldecode($params);
        $params = json_decode($params, true);
        return $params;
    }

    public function divideParam($value, $str)
    {

        preg_match_all("|{{" . $value . ":([^^]*?)}}|u", $str, $matches);
        return $matches[1];
    }

    /**
     * Get store template list
     * 
     * @access public 
     * @return object 
     * @param  judge 1:theme template; 0:customer template
     */
    public function getTemplateList($judge = 0)
    {
        $segment = new Segment('customer');
        $template = new StoreTemplateCollection;
        $r = new Retailer;
        $r->load($segment->get('customer')->getId(), 'customer_id');
        if ($judge == 0)
            $template->storeTemplateList($r['store_id']);
        else
            $template->storeTemplateList(0);
        return $template;
    }

    public function getProductDetailPageID($template_id)
    {
        $id = 0;
        $store_id = $this->judge_store_id();
        $template = new StoreTemplateCollection;
        $template->storeCustomizeTemplate($store_id, $template_id, 2);
        if (count($template)) {
            $id = $template[0]['id'];
        } else {
            $template = new StoreTemplate;
            $data = ['parent_id' => $template_id, 'store_id' => $store_id, 'page_type' => 2];
            $template->setData($data);
            $template->save();
            $id = $template->getId();
        }
        return $id;
    }

    public function getStorePicInfo($code, $current_store_id = null, $current_template_id = null, $part_id = null)
    {
        $Scollection = new StorePicInfoCollection;

        $store_id = $this->judge_store_id($current_store_id);
        $filters = ['resource_category_code' => $code, 'store_decoration_picinfo.store_id' => $store_id];

        if (!empty($current_template_id))
            $filters['template_id'] = $current_template_id;
        else
            $filters['template_id'] = null;

        if (!empty($part_id))
            $filters['part_id'] = $part_id;
        else
            $filters['part_id'] = null;

        $Scollection->where($filters)
                ->join('resource', 'store_decoration_picinfo.resource_id = resource.id', ['real_name'], 'left')->order(['resource.created_at' => 'DESC']);

        return $Scollection;
    }

    public function getCustomizeInfo($template_id, $page_type, $current_store_id = null)
    {
        $store_id = $this->judge_store_id($current_store_id);
        $template = new StoreTemplateCollection;
        $template->storeCustomizeTemplate($store_id, $template_id, $page_type);
        $r = new Retailer;
        $r->load($store_id, 'store_id');
        $url = $this->getBaseUrl() . $r->getStoreUrl();
        foreach ($template as $key => $value) {
            $template[$key]['url'] = $url . "?key=" . urlencode(base64_encode($value['id']));
        }
        return $template;
    }

    public function getStoreBanner($current_store_id = null, $current_retailer = null)
    {
        $store_id = $this->judge_store_id($current_store_id);
        if (empty($current_retailer)) {
            $segment = new Segment('customer');
            $customer_id = $segment->get('customer')['id'];
        } else {
            $customer_id = $current_retailer['customer_id'];
        }
        $retailer = new RetailerCollection;
        $retailer->where(['retailer.customer_id' => $customer_id, 'retailer.store_id' => $store_id])
                ->join('resource', 'retailer.banner = resource.id', ['real_name'], 'left')->order(['resource.created_at' => 'DESC']);
        //return $segment->get('customer');
        return empty($retailer) ? [] : $retailer[0];
    }

    public function getSearchProducts()
    {
        $condition = $this->getQuery();
        $products = new SalesProducts();
        $productsData = $products->getRetailerSalesProducts($condition);
        $content = "";
        foreach ($productsData as $key => $value) {
            $product = new product;
            $product->load($value['id']);
            $urls = $product->getUrl();
            $thumbnail = $products->getProduct($value['id'])->getThumbnail();
            if (strpos($thumbnail, 'http') === false) {
                $picURL = $this->getResourceUrl('image/resized/228x/' . $thumbnail);
            } else {
                $picURL = $thumbnail;
            }

            $content .= '<li class="col-md-3" >
                            <div>
                                <a href="' . $urls . '" target=_blank ><img class="pic" src="' . $picURL . '" width="100%" /></a>
                                <p class="price"><span class="actural">' . $products->getCurrency()->format($value['price']) . ' </span><span class="discount">' . $products->getCurrency()->format($value['price']) . '</span></p>
                                <h3 class="product-name"><a href="' . $urls . '" target=_blank >' . $value['name'] . '</a></h3>
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

    public function template_logo_top($params = '', $current_store_id = null, $current_retailer = null)
    {
        if (!empty($params)) {
//			if($params['widthSetType'] == "1")
//				$width = $params['widthSet']."px";
//			else
//				$width = '100%';
            $height = $params['heightSet'] . "px";
        } else {
            $width = '1128px';
            $height = '200px';
        }
        $retailer = $this->getStoreBanner($current_store_id, $current_retailer);
//		if(!empty($retailer['real_name']))
//			$content = '<img style="width:'.$width.';height:'.$height.'" src="'.$this->getBaseUrl('/pub/resource/image/'.$retailer['real_name']).'">';
//		else
//			$content = '<img style="width:'.$width.';height:'.$height.'" src="'.$this->getBaseUrl('/pub/theme/default/frontend/dragResource/images/text1.jpg').'">';
        if (!empty($retailer['real_name']))
            $content = $this->getBaseUrl('/pub/resource/image/' . $retailer['real_name']);
        else
            $content = $this->getBaseUrl('/pub/theme/default/frontend/dragResource/images/text1.jpg');
        return $content;
    }

    public function template_menu($params = '', $current_store_id = null)
    {
        $store_id = $this->judge_store_id($current_store_id);
        $r = new Retailer;
        $r->load($store_id, 'store_id');
        $url = $this->getBaseUrl() . $r->getStoreUrl();
        $result = $this->getStorePicInfo('menu', $current_store_id);
        $content = '<li class="menu" ><a  href="' . $url . '">首 页</a></li>';
        foreach ($result as $key => $value) {
            if (trim($value['url']) != "")
                $content .= '<li class="menu" ><a  href="' . $value['url'] . '">' . $value['pic_title'] . '</a></li>';
            else
                $content .= '<li class="menu" ><a  href="javascript:void(0)">' . $value['pic_title'] . '</a></li>';
        }
        return $content;
    }

    public function template_paragraph($params = '', $current_store_id = null)
    {
        $content = '<p> <br><br>可以在此模块中通过编辑器自由输入文字以及编排格式<br><br> </p>';
        return $content;
    }

    public function template_long_search($params = '', $current_store_id = null)
    {
        $content = '<form target=_blank action="' . $this->getBaseUrl('/retailer/store/viewSearch') . '"><label class="search-label">本店搜索</label>
                 <input class="keyword" type="text" name="name" value="" />&nbsp;&nbsp;
                <input class="price-from" type="text" name="price_from" value="" />
                &nbsp;-&nbsp;
                <input class="price-to" type="text" name="price_to" value="" />
                <button class="search-button">搜索</button>
                </form>';
        return $content;
    }

    public function template_short_search($params = '', $current_store_id = null)
    {
        $content = '<div class="title"><h2>本店搜索</h2></div>
                <div class="search-table">
                    <form target=_blank action="' . $this->getBaseUrl('/retailer/store/viewSearch') . '">
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

    public function template_product_class($params = '', $current_store_id = null)
    {
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

    public function template_sales_amount($params = '', $current_store_id = null)
    {
        if (!empty($params)) {
            $params = urldecode($params);
            $params = json_decode($params, true);
        }


        //$hot_text = empty($params) ? "" : $params['hot_text'];
        //$price_from = empty($params) ? "" : $params['price_from'];
        //$price_to = empty($params) ? "" : $params['price_to'];
        $select_row = empty($params) ? 3 : $params['select_row'];
        //$product_ids = empty($params['product_ids']) ? "" : $params['product_ids'] ;
        //$condition['name'] = $hot_text;
        $condition['limit'] = $select_row;
        //$condition['price_from'] = $price_from;
        //$condition['price_to'] = $price_to;
        //$condition['product_ids'] = $product_ids;
        $products = new SalesProducts();
        $productsData = $products->getRetailerSalesProducts($condition, $current_store_id);


        $content = '<ul>';

        foreach ($productsData as $key => $value) {
            $product = new product;
            $product->load($value['id']);
            $urls = $product->getUrl();
            $thumbnail = $products->getProduct($value['id'])->getThumbnail();
            if (strpos($thumbnail, 'http') === false) {
                $picURL = $this->getResourceUrl('image/resized/228x/' . $thumbnail);
            } else {
                $picURL = $thumbnail;
            }


            $content .= '<li>
                            <div class="col-md-4" style="padding:3px" >
                                <a href="' . $urls . '" target=_blank ><img class="pic" src="' . $picURL . '"  /></a>
                                </div>
                                <div class="col-md-8" style="padding:3px" >
                                    <div class="pad-5" style="padding:2px" >
                                        <h3 class="product-name"><a href="' . $urls . '" target=_blank >' . $value['name'] . '</a></h3>
                                        <p class="price"><span class="actural">' . $products->getCurrency()->format($value['price']) . '</span></p>                                      
                                    </div>
                            </div>
                </li>';
        }

        $content .= '</ul>';
        return $content;
    }

    public function template_hot_product($params = '', $current_store_id = null)
    {

        if (!empty($params)) {
            $params = urldecode($params);
            $params = json_decode($params, true);
        }


        $hot_text = empty($params) ? "" : $params['hot_text'];
        $price_from = empty($params) ? "" : $params['price_from'];
        $price_to = empty($params) ? "" : $params['price_to'];
        $select_row = empty($params) ? 1 : $params['select_row'];
        $select_column = empty($params) ? 4 : (int) $params['select_column'];

        $product_ids = empty($params['product_ids']) ? "" : $params['product_ids'];
        $select_col_md = 'col-md-3';
        $pic_height = '225px';
        switch ($select_column) {
            case 3:
                $select_col_md = 'col-md-4';
                $pic_height = '313px';
                break;
            case 2:
                $select_col_md = 'col-md-6';
                $pic_height = '490px';
                break;
            case 1:
                $select_col_md = 'col-md-12';
                $pic_height = '';
                break;
            default:
                $select_col_md = 'col-md-3';
                $pic_height = '225px';
                break;
        }

        $condition['name'] = $hot_text;
        $condition['limit'] = $select_column * $select_row;
        $condition['price_from'] = $price_from;
        $condition['price_to'] = $price_to;
        $condition['product_ids'] = $product_ids;
        $products = new SalesProducts();
        $productsData = $products->getRetailerSalesProducts($condition, $current_store_id);


        $content = '<ul>';
        foreach ($productsData as $key => $value) {
            $product = new product;
            $product->load($value['id']);
            $urls = $product->getUrl();
            $thumbnail = $products->getProduct($value['id'])->getThumbnail();
            if (strpos($thumbnail, 'http') === false) {
                $picURL = $this->getResourceUrl('image/resized/228x/' . $thumbnail);
            } else {
                $picURL = $thumbnail;
            }

            $content .= '<li class="' . $select_col_md . '">
                            <div>
                                <a href="' . $urls . '" target=_blank ><img class="pic"  src="' . $picURL . '"  /></a>
                                <p class="price"><span class="actural">' . $products->getCurrency()->format($value['price']) . ' </span><span class="discount">' . $products->getCurrency()->format($value['price']) . '</span></p>
                                <h3 class="product-name"><a href="' . $urls . '" target=_blank >' . $value['name'] . '</a></h3>
                                <p class="paid-count"></p>
                            </div>
                        </li>';
        }
        $content .= '</ul>';
        return $content;
    }

    public function func_getProductInfo($condition, $current_store_id = null)
    {
        $products = new SalesProducts();
        $productsData = $products->getRetailerSalesProducts($condition, $current_store_id);
        $productsDataCount = $products->getRetailerSalesProductsCount($condition, $current_store_id);
        foreach ($productsData as $key => $value) {
            $product = new product;
            $product->load($value['id']);
            $urls = $product->getUrl();
            $thumbnail = $products->getProduct($value['id'])->getThumbnail();
            if (strpos($thumbnail, 'http') === false) {
                $picURL = $this->getResourceUrl('image/' . $thumbnail);
            } else {
                $picURL = $thumbnail;
            }
            $productsData[$key]['productURL'] = $urls;
            $productsData[$key]['picURL'] = $picURL;
            $productsData[$key]['acturalPrice'] = $products->getCurrency()->format($value['price']);
        }
        return ['data' => $productsData, 'count' => $productsDataCount];
    }

    public function template_store_recommend($params = '', $current_store_id = null)
    {

        if (!empty($params)) {
            $params = urldecode($params);
            $params = json_decode($params, true);
        }

        $hot_text = empty($params) ? "" : $params['hot_text'];
        $price_from = empty($params) ? "" : $params['price_from'];
        $price_to = empty($params) ? "" : $params['price_to'];
        $product_ids = empty($params['product_ids']) ? "" : $params['product_ids'];

        $condition['name'] = $hot_text;
        $condition['limit'] = 9;
        $condition['price_from'] = $price_from;
        $condition['price_to'] = $price_to;
        $condition['product_ids'] = $product_ids;

        $result = $this->func_getProductInfo($condition, $current_store_id);
        $products = $result['data'];


        $content = '';
        for ($i = 0; $i < 3; $i++) {
            $content .= '<div class="col-md-4">';

            if ($i == 1) {
                if (!empty($products[0]))
                    $content .= '<div class="prompt-big">
                                <a href="' . $products[0]['productURL'] . '" target=_blank ><img class="pic" src="' . $products[0]['picURL'] . '"  /></a>
                                <p class="price"><span class="actural">' . $products[0]['acturalPrice'] . ' </span><span class="discount">' . $products[0]['acturalPrice'] . '</span></p>
                                <h3 class="product-name"><a href="' . $products[0]['productURL'] . '" target=_blank >' . $products[0]['name'] . '</a></h3>
                            </div>';
                else
                    $content .= '<div class="prompt-big"></div>';
            }else {
                for ($j = 0; $j < 4; $j++) {
                    if ($i == 0)
                        $index = $j + 1;
                    if ($i == 2)
                        $index = $j + 5;
                    if (!empty($products[$index]))
                        $content .= '<div class="col-md-6 col-md-61">
                        <div class="prompt-small"><a href="' . $products[$index]['productURL'] . '" target=_blank ><img class="pic" src="' . $products[$index]['picURL'] . '"  /></a></div>
                    </div>';
                    else
                        $content .= '<div class="col-md-6 col-md-61"></div>';
                }
            }


            $content .= '</div>';
        }

        return $content;
    }

    public function template_product_recommend($params = '', $current_store_id = null)
    {
        if (!empty($params)) {
            $params = urldecode($params);
            $params = json_decode($params, true);
        }

        $product_ids = empty($params['product_ids']) ? "" : $params['product_ids'];
        $hot_text = empty($params) ? "" : $params['hot_text'];
        $price_from = empty($params) ? "" : $params['price_from'];
        $price_to = empty($params) ? "" : $params['price_to'];
        $select_row = empty($params) ? 1 : $params['select_row'];
        $select_column = empty($params) ? 4 : (int) $params['select_column'];
        $select_col_md = 'col-md-3';
        $pic_height = '225px';
        switch ($select_column) {
            case 3:
                $select_col_md = 'col-md-4';
                $pic_height = '313px';
                break;
            case 2:
                $select_col_md = 'col-md-6';
                $pic_height = '490px';
                break;
            case 1:
                $select_col_md = 'col-md-12';
                $pic_height = '';
                break;
            default:
                $select_col_md = 'col-md-3';
                $pic_height = '225px';
                break;
        }

        $condition['name'] = $hot_text;
        $condition['limit'] = $select_column * $select_row;
        $condition['price_from'] = $price_from;
        $condition['price_to'] = $price_to;
        $condition['product_ids'] = $product_ids;
        //$condition['recomend_status'] = "1";
        $products = new SalesProducts();
        $productsData = $products->getRetailerSalesProducts($condition, $current_store_id);


        $content = '<ul>';
        foreach ($productsData as $key => $value) {
            $product = new product;
            $product->load($value['id']);
            $urls = $product->getUrl();
            $thumbnail = $products->getProduct($value['id'])->getThumbnail();
            if (strpos($thumbnail, 'http') === false) {
                $picURL = $this->getResourceUrl('image/resized/228x/' . $thumbnail);
            } else {
                $picURL = $thumbnail;
            }

            $content .= '<li class="' . $select_col_md . '">
                            <div>
                                <a href="' . $urls . '" target=_blank ><img class="pic" src="' . $picURL . '"  /></a>
                                <p class="price"><span class="actural" style="width:80%" >' . $products->getCurrency()->format($value['price']) . ' </span></p>
                                <h3 class="product-name"><a href="' . $urls . '" target=_blank >' . $value['name'] . '</a></h3>
                       
                            </div>
                        </li>';
        }
        $content .= '</ul>';
        return $content;
    }

    public function template_pic_carousel($params = '', $current_store_id = null)
    {
        if (!empty($params)) {
            $params = urldecode($params);
            $params = json_decode($params, true);
        }

        $current_template_id = empty($params) ? "" : $params['current_template_id'];
        $part_id = empty($params) ? "" : $params['part_id'];

        $content = '<div class="carousel_wrap"><ul class="hiSlider hiSlider3">';
        $result = $this->getStorePicInfo('store_carousel', $current_store_id, $current_template_id, $part_id);
        foreach ($result as $key => $value) {
            if (trim($value['url']) != "")
                $content .= '<li class="hiSlider-item"><a href="' . $value['url'] . '" target=_blank ><img src="' . $this->getBaseUrl('/pub/resource/image/' . $value['real_name']) . '" alt="' . $value['pic_title'] . '"></a></li>';
            else
                $content .= '<li class="hiSlider-item"><img src="' . $this->getBaseUrl('/pub/resource/image/' . $value['real_name']) . '" alt="' . $value['pic_title'] . '"></li>';
        }
        $content .= '</ul></div>';
        return $content;
    }

}
