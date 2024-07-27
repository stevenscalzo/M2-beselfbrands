<?php

namespace Beself\Customer\Setup;

use Beself\Customer\Model\Config\Source\PreferredProductType;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory      $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
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
            $this->addColumnInCustomerGroupsTable($setup);
        }

        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $this->createCustomerAttribute($setup);
        }

        if (version_compare($context->getVersion(), '0.0.4', '<')) {
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->removeAttribute(Customer::ENTITY, 'preferred_product_type');

            $this->addAttribute($customerSetup);
        }

        $setup->endSetup();
    }

    /**
     * @throws \Zend_Validate_Exception
     * @throws LocalizedException
     * @throws \Exception
     */
    protected function addAttribute(CustomerSetup $customerSetup): void
    {
        $customerSetup->addAttribute(Customer::ENTITY,
            "preferred_product_type",
            [
                'type' => "varchar",
                'label' => "Preferred Product Type",
                'input' => "select",
                'source' => PreferredProductType::class,
                'required' => false,
                'visible' => true,
                'position' => 999,
                'system' => false,
                'backend' => '',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY,
            "preferred_product_type")
            ->addData(['used_in_forms' => [
                'adminhtml_customer',
                'adminhtml_checkout',
                'customer_account_create',
                'customer_account_edit'
            ]]);

        $attribute->save();
    }

    /**
     * @throws \Zend_Validate_Exception
     * @throws LocalizedException
     */
    public function createCustomerAttribute($setup)
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'preferred_product_type',
            [
                'type' => 'varchar',
                'label' => 'Preferred Product Type',
                'input' => 'select',
                'source' => PreferredProductType::class,
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'system' => false,
                'position' => 999,
                'sort_order' => 999,
                'default' => ''
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'preferred_product_type')
            ->addData([
                'used_in_forms' => [
                    'adminhtml_customer',
                    'customer_account_create',
                    'customer_account_edit'
                ]
            ]);

        $attribute->save();
    }

    public function addColumnInCustomerGroupsTable($setup)
    {
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
}
