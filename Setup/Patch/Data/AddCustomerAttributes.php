<?php
declare(strict_types=1);

namespace GroomerShop\AddressFields\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddCustomerAttributes implements DataPatchInterface, PatchRevertableInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly CustomerSetupFactory $customerSetupFactory,
        private readonly AttributeSetFactory $attributeSetFactory
    ) {}

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = (int) $customerEntity->getDefaultAttributeSetId();
        $attributeGroupId = (int) $this->attributeSetFactory->create()->getDefaultGroupId($attributeSetId);

        $this->addBreederStatus($customerSetup, $attributeSetId, $attributeGroupId);
        $this->addWholesalerStatus($customerSetup, $attributeSetId, $attributeGroupId);
        $this->addAdminComments($customerSetup, $attributeSetId, $attributeGroupId);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    private function addBreederStatus(CustomerSetup $customerSetup, int $attributeSetId, int $attributeGroupId): void
    {
        $customerSetup->addAttribute(Customer::ENTITY, 'breeder_status', [
            'type'                  => 'int',
            'label'                 => 'Breeder Status',
            'input'                 => 'boolean',
            'required'              => false,
            'default'               => '0',
            'visible'               => true,
            'user_defined'          => true,
            'sort_order'            => 90,
            'position'              => 90,
            'system'                => false,
            'is_used_in_grid'       => true,
            'is_visible_in_grid'    => true,
            'is_filterable_in_grid' => true,
            'is_searchable_in_grid' => false,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'breeder_status');
        $attribute->addData([
            'attribute_set_id'   => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms'      => ['customer_account_create', 'adminhtml_customer'],
        ]);
        $attribute->save();
    }

    private function addWholesalerStatus(CustomerSetup $customerSetup, int $attributeSetId, int $attributeGroupId): void
    {
        $customerSetup->addAttribute(Customer::ENTITY, 'wholesaler_status', [
            'type'                  => 'int',
            'label'                 => 'Wholesaler Status',
            'input'                 => 'boolean',
            'required'              => false,
            'default'               => '0',
            'visible'               => true,
            'user_defined'          => true,
            'sort_order'            => 110,
            'position'              => 110,
            'system'                => false,
            'is_used_in_grid'       => true,
            'is_visible_in_grid'    => true,
            'is_filterable_in_grid' => true,
            'is_searchable_in_grid' => false,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'wholesaler_status');
        $attribute->addData([
            'attribute_set_id'   => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms'      => ['customer_account_create', 'adminhtml_customer'],
        ]);
        $attribute->save();
    }

    private function addAdminComments(CustomerSetup $customerSetup, int $attributeSetId, int $attributeGroupId): void
    {
        $customerSetup->addAttribute(Customer::ENTITY, 'admin_comments', [
            'type'                  => 'text',
            'label'                 => 'Admin Comments',
            'input'                 => 'textarea',
            'required'              => false,
            'default'               => '',
            'visible'               => true,
            'user_defined'          => true,
            'sort_order'            => 160,
            'position'              => 160,
            'system'                => false,
            'is_used_in_grid'       => false,
            'is_visible_in_grid'    => false,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => false,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'admin_comments');
        $attribute->addData([
            'attribute_set_id'   => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms'      => ['adminhtml_customer'],
        ]);
        $attribute->save();
    }

    public function revert(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->removeAttribute(Customer::ENTITY, 'breeder_status');
        $customerSetup->removeAttribute(Customer::ENTITY, 'wholesaler_status');
        $customerSetup->removeAttribute(Customer::ENTITY, 'admin_comments');

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
