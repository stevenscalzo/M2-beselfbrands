<?php

namespace Beself\Customer\Setup;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\GroupFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;

class UpgradeData implements UpgradeDataInterface
{
    private $groupFactory;
    private $groupRepository;
    private $eavSetupFactory;
    private $eavConfig;

    public function __construct(
        GroupFactory $groupFactory,
        GroupRepositoryInterface $groupRepository,
        EavSetupFactory $eavSetupFactory,
        EavConfig $eavConfig
    ) {
        $this->groupFactory = $groupFactory;
        $this->groupRepository = $groupRepository;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $connection = $setup->getConnection();
            $customerGroupTable = $setup->getTable('customer_group');

            if ($connection->tableColumnExists($customerGroupTable, 'is_distributor') === false) {
                $connection->addColumn(
                    $customerGroupTable,
                    'is_distributor',
                    [
                        'type' => Table::TYPE_BOOLEAN,
                        'nullable' => false,
                        'default' => '0',
                        'comment' => 'Is Distributor'
                    ]
                );
            }
        }

        $setup->endSetup();
    }
}
