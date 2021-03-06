<?php
namespace Boxalino\Intelligence\Block;

/**
 * Class BxRecommendationBlock
 * @package Boxalino\Intelligence\Block
 */
Class BxBannerBlock extends BxRecommendationBlock implements \Magento\Framework\DataObject\IdentityInterface{

    protected $_logger;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Boxalino\Intelligence\Helper\P13n\Adapter $p13nHelper,
        \Boxalino\Intelligence\Helper\Data $bxHelperData,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory $factory,
        \Magento\Framework\App\Request\Http $request,
        array $data
        )

        {

        $this->_logger = $context->getLogger();
        parent::__construct($context, 
                            $p13nHelper,
                            $bxHelperData,
                            $checkoutSession, 
                            $catalogProductVisibility, 
                            $factory, 
                            $request,
                            $data);

        } 

    protected function _prepareData(){
        
        return $this;
    }

    protected function prepareRecommendations($recommendations = array(), $returnFields = array()){
        parent::prepareRecommendations($recommendations, array('title', 'products_bxi_bxi_jssor_slide', 'products_bxi_bxi_jssor_transition', 'products_bxi_bxi_name', 'products_bxi_bxi_jssor_control', 'products_bxi_bxi_jssor_break'));
    }

    public function isActive(){

        if ($this->bxHelperData->isBannerEnabled()) {
            
            return true;

        }

    }

    public function check(){

        $values = array(

        0 => $this->getBannerSlides(),
        1 => $this->getBannerJssorId(),
        2 => $this->getBannerJssorSlideTransitions(),
        3 => $this->getBannerJssorSlideBreaks(),
        4 => $this->getBannerJssorSlideControls(),
        5 => $this->getBannerJssorOptions(),
        6 => $this->getBannerJssorMaxWidth(),
        7 => $this->getBannerJssorCSS(),
        8 => $this->getBannerJssorStyle(),
        9 => $this->getBannerJssorLoadingScreen(),
        10 => $this->getBannerJssorSlidesStyle(),
        11 => $this->getBannerJssorBulletNavigator(),
        12 => $this->getBannerJssorArrowNavigator(),
        13 => $this->getBannerFunction()

        );

        if (!in_array('', $values)) {
            
            return true;
        
        }else{

            foreach ($values as $key => $value) {
                
                try{

                    if($value == '') {
                
                    throw new \Exception("Function $key returned empty.");

                    }

                } catch(\Exception $e){

                    $this->setFallback(true);

                    $this->_logger->critical($e);
                }

            }

            return false;

        }

    }

    public function getBannerSlides() {

        $slides = $this->p13nHelper->getClientResponse()->getHitFieldValues(array('products_bxi_bxi_jssor_slide', 'products_bxi_bxi_name'), $this->_data['widget']);
        $counters = array();
        foreach($slides as $id => $vals) {
            $slides[$id]['div'] = $this->getBannerSlide($id, $vals, $counters);
        }
        return $slides;
    }

    public function getBannerSlide($id, $vals, &$counters) {
        $language = $this->p13nHelper->getLanguage();
        if(isset($vals['products_bxi_bxi_jssor_slide']) && sizeof($vals['products_bxi_bxi_jssor_slide']) > 0) {
            $json = $vals['products_bxi_bxi_jssor_slide'][0];

            $slide = json_decode($json, true);
            if(isset($slide[$language])) {
                $json = $slide[$language];

                for($i=1; $i<10; $i++) {

                    if(!isset($counters[$i])) {
                        $counters[$i] = 0;
                    }

                    $pieces = explode('BX_COUNTER'.$i, $json);
                    foreach($pieces as $j => $piece) {
                        if($j >= sizeof($pieces)-1) {
                            continue;
                        }
                        $pieces[$j] .= $counters[$i]++;
                    }
                    $json = implode('', $pieces);
                
                }
                return $json;
            }
        }
        return '';
    }

    public function getBannerJssorSlideGenericJS($key) {
        $language = $this->p13nHelper->getLanguage();
        
        $slides = $this->p13nHelper->getClientResponse()->getHitFieldValues(array($key), $this->_data['widget']);

        $jsArray = array();
        foreach($slides as $id => $vals) {
            if(isset($vals[$key]) && sizeof($vals[$key]) > 0) {

                $jsons = json_decode($vals[$key][0], true);
                if(isset($jsons[$language])) {
                    $json = $jsons[$language];

                    //fix some special case an extra '}' appears wrongly at the end
                    $minus = 2;
                    if(substr($json, strlen($json)-1, 1) == '}') {
                        $minus = 3;
                    }

                    //removing the extra [] around
                    $json = substr($json, 1, strlen($json)-$minus);

                    $jsArray[] = $json;
                }
            }
        }
        
        return '[' . implode(',', $jsArray) . ']';
    }

    public function getBannerJssorSlideTransitions() {

        return $this->getBannerJssorSlideGenericJS('products_bxi_bxi_jssor_transition');
    
    }

    public function getBannerJssorSlideBreaks() {

        return $this->getBannerJssorSlideGenericJS('products_bxi_bxi_jssor_break');

    }

    public function getBannerJssorSlideControls() {

        return $this->getBannerJssorSlideGenericJS('products_bxi_bxi_jssor_control');
    
    }

    public function getBannerJssorOptions() {

        $bannerJssorOptions = $this->p13nHelper->getClientResponse()->getExtraInfo('banner_jssor_options');

        return $bannerJssorOptions;
    }

    public function getBannerJssorId() {

        $bannerJssorId = $this->p13nHelper->getClientResponse()->getExtraInfo('banner_jssor_id');

        return $bannerJssorId;
    }

    public function getBannerJssorStyle() {

        $bannerJssorMaxWidth = $this->p13nHelper->getClientResponse()->getExtraInfo('banner_jssor_style');
        
        return $bannerJssorMaxWidth;
    }

    public function getBannerJssorSlidesStyle() {

        $bannerJssorMaxWidth = $this->p13nHelper->getClientResponse()->getExtraInfo('banner_jssor_slides_style');

        return $bannerJssorMaxWidth;
    }

    public function getBannerJssorMaxWidth() {

        $bannerJssorMaxWidth = $this->p13nHelper->getClientResponse()->getExtraInfo('banner_jssor_max_width');

        return $bannerJssorMaxWidth;
    }

    public function getBannerJssorCSS() {

        $bannerJssorCss = $this->p13nHelper->getClientResponse()->getExtraInfo('banner_jssor_css');

        return str_replace("JSSORID", $this->getBannerJssorId(), $bannerJssorCss);
    }

    public function getBannerJssorLoadingScreen() {

        $bannerJssorLoadingScreen = $this->p13nHelper->getClientResponse()->getExtraInfo('banner_jssor_loading_screen');

        return $bannerJssorLoadingScreen;
    }

    public function getBannerJssorBulletNavigator() {

        $bannerJssorBulletNavigator = $this->p13nHelper->getClientResponse()->getExtraInfo('banner_jssor_bullet_navigator');

        return $bannerJssorBulletNavigator;

    }

    public function getBannerJssorArrowNavigator() {

        $bannerJssorArrowNavigator = $this->p13nHelper->getClientResponse()->getExtraInfo('banner_jssor_arrow_navigator');

        return $bannerJssorArrowNavigator;

    }

    public function getBannerFunction() {

        $bannerFunction = $this->p13nHelper->getClientResponse()->getExtraInfo('banner_jssor_function');

        return $bannerFunction;

    }

    public function getHitCount(){

      $hitCount = sizeof($this->p13nHelper->getClientResponse()->getHitIds());

      return $hitCount;

    }

}
