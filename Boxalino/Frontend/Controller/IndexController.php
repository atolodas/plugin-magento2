<?php
namespace Boxalino\Frontend\Controller;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;

class IndexController extends \Magento\CatalogSearch\Controller\Result\Index
{
    protected $config;
    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        \Magento\Framework\ObjectManager\Config\Config $config,
        Resolver $layerResolver)
    {
        $this->config = $config;
        parent::__construct($context, $catalogSession, $storeManager, $queryFactory, $layerResolver);
    }

    public function execute()
    {

        $configuration = array('Magento\CatalogSearch\Block\SearchResult\ListProduct' =>
            array('type'=>'Boxalino\Frontend\Block\Product\BxListProducts')
        );
        $this->_objectManager->configure($configuration);
        $configuration = array('Magento\Catalog\Model\Layer\FilterList' =>
            array('type'=>'Boxalino\Frontend\Model\FilterList')
        );
        $configuration = array('searchFilterList' =>
            array('type'=>'Boxalino\Frontend\Model\FilterList')
        );
        $this->_objectManager->configure($configuration);
        return parent::execute(); // TODO: Change the autogenerated stub

    }

}

?>