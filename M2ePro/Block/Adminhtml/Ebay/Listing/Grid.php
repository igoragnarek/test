<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Ebay\Listing;

/**
 * Class \Ess\M2ePro\Block\Adminhtml\Ebay\Listing\Grid
 */
class Grid extends \Ess\M2ePro\Block\Adminhtml\Listing\Grid
{
    const MASS_ACTION_ID_EDIT_PARTS_COMPATIBILITY = 'editPartsCompatibilityMode';

    protected $ebayListingResourceModel;
    protected $ebayFactory;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Ebay\Listing $ebayListingResourceModel,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Ebay\Factory $ebayFactory,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->ebayListingResourceModel = $ebayListingResourceModel;
        $this->ebayFactory = $ebayFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    //########################################

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingGrid');
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        $aTable = $this->activeRecordFactory->getObject('Account')->getResource()->getMainTable();
        $mTable = $this->activeRecordFactory->getObject('Marketplace')->getResource()->getMainTable();

        // Get collection of listings
        $collection = $this->ebayFactory->getObject('Listing')->getCollection();
        $collection->getSelect()->join(
            ['a' => $aTable],
            '(`a`.`id` = `main_table`.`account_id`)',
            ['account_title' => 'title']
        );
        $collection->getSelect()->join(
            ['m' => $mTable],
            '(`m`.`id` = `main_table`.`marketplace_id`)',
            ['marketplace_title' => 'title']
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    //########################################

    protected function _prepareLayout()
    {
        $this->css->addFile('ebay/listing/grid.css');

        return parent::_prepareLayout();
    }

    //########################################

    protected function getColumnActionsItems()
    {
        $backUrl = $this->getHelper('Data')->makeBackUrlParam('*/ebay_listing/index');

        return [
            'manageProducts' => [
                'caption' => $this->__('Manage'),
                'group'   => 'products_actions',
                'field'   => 'id',
                'url'     => [
                    'base'   => '*/ebay_listing/view',
                    'params' => ['id' => $this->getId(), 'back' => $backUrl]
                ]
            ],

            'addProductsSourceProducts' => [
                'caption'        => $this->__('Add From Products List'),
                'group'          => 'products_actions',
                'field'          => 'id',
                'onclick_action' => 'EbayListingGridObj.addProductsSourceProductsAction',
            ],

            'addProductsSourceCategories' => [
                'caption'        => $this->__('Add From Categories'),
                'group'          => 'products_actions',
                'field'          => 'id',
                'onclick_action' => 'EbayListingGridObj.addProductsSourceCategoriesAction',
            ],

            'autoActions' => [
                'caption' => $this->__('Auto Add/Remove Rules'),
                'group'   => 'products_actions',
                'field'   => 'id',
                'url'     => [
                    'base'   => '*/ebay_listing/view',
                    'params' => ['id' => $this->getId(), 'auto_actions' => 1]
                ]
            ],

            'editTitle' => [
                'caption'        => $this->__('Title'),
                'group'          => 'edit_actions',
                'field'          => 'id',
                'onclick_action' => 'EditListingTitleObj.openPopup',
            ],

            'editConfiguration' => [
                'caption' => $this->__('Configuration'),
                'group'   => 'edit_actions',
                'field'   => 'id',
                'url'     => [
                    'base'   => '*/ebay_listing/edit',
                    'params' => ['back' => $backUrl]
                ]
            ],

            self::MASS_ACTION_ID_EDIT_PARTS_COMPATIBILITY => [
                'caption'        => $this->__('Parts Compatibility Mode'),
                'group'          => 'edit_actions',
                'field'          => 'id',
                'onclick_action' => 'EditCompatibilityModeObj.openPopup',
                'action_id'      => self::MASS_ACTION_ID_EDIT_PARTS_COMPATIBILITY
            ],

            'viewLogs' => [
                'caption' => $this->__('Logs & Events'),
                'group'   => 'other',
                'field'   => \Ess\M2ePro\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_ID_FIELD,
                'url'     => [
                    'base' => '*/ebay_log_listing_product/index'
                ]
            ],

            'clearLogs' => [
                'caption' => $this->__('Clear Log'),
                'confirm' => $this->__('Are you sure?'),
                'group'   => 'other',
                'field'   => 'id',
                'url'     => [
                    'base'   => '*/listing/clearLog',
                    'params' => [
                        'back' => $backUrl
                    ]
                ]
            ],

            'delete' => [
                'caption' => $this->__('Delete Listing'),
                'confirm' => $this->__('Are you sure?'),
                'group'   => 'other',
                'field'   => 'id',
                'url'     => [
                    'base'   => '*/ebay_listing/delete',
                    'params' => ['id' => $this->getId()]
                ]
            ],
        ];
    }

    /**
     * editPartsCompatibilityMode has to be not accessible for not Multi Motors marketplaces
     * @return $this
     */
    protected function _prepareColumns()
    {
        $result = parent::_prepareColumns();

        $this->getColumn('actions')->setData(
            'renderer',
            '\Ess\M2ePro\Block\Adminhtml\Ebay\Listing\Grid\Column\Renderer\Action'
        );

        return $result;
    }

    //########################################

    public function callbackColumnTotalProducts($value, $row, $column, $isExport)
    {
        $value = $this->ebayListingResourceModel->getStatisticTotalCount($row['id']);

        if ($value == 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    //########################################

    public function callbackColumnListedProducts($value, $row, $column, $isExport)
    {
        $value = $this->ebayListingResourceModel->getStatisticActiveCount($row['id']);

        if ($value == 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    //########################################

    public function callbackColumnInactiveProducts($value, $row, $column, $isExport)
    {
        $value = $this->ebayListingResourceModel->getStatisticInactiveCount($row['id']);

        if ($value == 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $title = $this->getHelper('Data')->escapeHtml($value);
        $compatibilityMode = $row->getChildObject()->getData('parts_compatibility_mode');

        $value = <<<HTML
<span id="listing_title_{$row->getId()}">
    {$title}
</span>
<span id="listing_compatibility_mode_{$row->getId()}" style="display: none;">
    {$compatibilityMode}
</span>
HTML;

        /** @var $row \Ess\M2ePro\Model\Listing */
        $accountTitle = $row->getData('account_title');
        $marketplaceTitle = $row->getData('marketplace_title');

        $storeModel = $this->_storeManager->getStore($row->getStoreId());
        $storeView = $this->_storeManager->getWebsite($storeModel->getWebsiteId())->getName();
        if (strtolower($storeView) != 'admin') {
            $storeView .= ' > ' . $this->_storeManager->getGroup($storeModel->getStoreGroupId())->getName();
            $storeView .= ' > ' . $storeModel->getName();
        } else {
            $storeView = $this->__('Admin (Default Values)');
        }

        $account = $this->__('Account');
        $marketplace = $this->__('Marketplace');
        $store = $this->__('Magento Store View');

        $value .= <<<HTML
<div>
    <span style="font-weight: bold">{$account}</span>: <span style="color: #505050">{$accountTitle}</span><br/>
    <span style="font-weight: bold">{$marketplace}</span>: <span style="color: #505050">{$marketplaceTitle}</span><br/>
    <span style="font-weight: bold">{$store}</span>: <span style="color: #505050">{$storeView}</span>
</div>
HTML;

        return $value;
    }

    public function callbackColumnSoldQTY($value, $row, $column, $isExport)
    {
        return $this->getColumnValue($row->getChildObject()->getItemsSoldCount());
    }

    //########################################

    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/ebay_listing/view',
            [
                'id' => $row->getId()
            ]
        );
    }

    //########################################

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'main_table.title LIKE ? OR a.title LIKE ? OR m.title LIKE ?',
            '%' . $value . '%'
        );
    }

    //########################################

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::_toHtml();
        }

        $this->jsUrl->addUrls(
            array_merge(
                $this->getHelper('Data')->getControllerActions('Ebay\Listing'),
                $this->getHelper('Data')->getControllerActions('Ebay_Listing_Product_Add'),
                $this->getHelper('Data')->getControllerActions('Ebay_Log_Listing_Product'),
                $this->getHelper('Data')->getControllerActions('Ebay\Template')
            )
        );

        $this->jsUrl->add($this->getUrl('*/listing/edit'), 'listing/edit');

        $this->jsTranslator->add('Edit Listing Title', $this->__('Edit Listing Title'));
        $this->jsTranslator->add('Edit Parts Compatibility Mode', $this->__('Edit Parts Compatibility Mode'));
        $this->jsTranslator->add('Listing Title', $this->__('Listing Title'));
        $this->jsTranslator->add(
            'The specified Title is already used for other Listing. Listing Title must be unique.',
            $this->__(
                'The specified Title is already used for other Listing. Listing Title must be unique.'
            )
        );

        $this->jsPhp->addConstants(
            $this->getHelper('Data')->getClassConstants(\Ess\M2ePro\Helper\Component\Ebay::class)
        );

        $component = \Ess\M2ePro\Helper\Component\Ebay::NICK;

        $this->js->add(
            <<<JS
    require([
        'M2ePro/Ebay/Listing/Grid',
        'M2ePro/Listing/EditTitle',
        'M2ePro/Ebay/Listing/EditCompatibilityMode'
    ], function(){
        window.EbayListingGridObj = new EbayListingGrid('{$this->getId()}');
        window.EditListingTitleObj = new ListingEditListingTitle('{$this->getId()}', '{$component}');
        window.EditCompatibilityModeObj = new EditCompatibilityMode('{$this->getId()}');
    });
JS
        );

        return parent::_toHtml();
    }

    //########################################
}
