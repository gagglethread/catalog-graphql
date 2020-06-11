<?php


namespace ScandiPWA\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessor;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessor\StockProcessor as MagentoStockProcessor;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResource;
use Magento\Framework\Api\SearchCriteriaInterface;

class StockProcessor extends MagentoStockProcessor
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfig;
    
    /**
     * @var StockStatusResource
     */
    private $stockStatusResource;

    /**
     * @param StockConfigurationInterface $stockConfig
     * @param StockStatusResource         $stockStatusResource
     */
    public function __construct(
        StockConfigurationInterface $stockConfig,
        StockStatusResource $stockStatusResource
    ) {
        $this->stockConfig = $stockConfig;
        $this->stockStatusResource = $stockStatusResource;
    }

    /**
     * Motivation: to skip checking stock if the visibility or status is marked for filter
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return bool
     */
    protected function getIsShouldFilterBeApplied(SearchCriteriaInterface $searchCriteria): Bool {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                // make sure there is no status and visibility filters
                if (in_array($filter->getField(), ['status', 'visibility'])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames
    ): Collection {
        $isShouldFilterBeApplied = $this->getIsShouldFilterBeApplied($searchCriteria);

        if (!$this->stockConfig->isShowOutOfStock() && $isShouldFilterBeApplied) {
            $this->stockStatusResource->addIsInStockFilterToCollection($collection);
        }

        return $collection;
    }
}