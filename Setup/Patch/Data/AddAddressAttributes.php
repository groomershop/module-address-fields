<?php
declare(strict_types=1);

namespace GroomerShop\AddressFields\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddAddressAttributes implements DataPatchInterface, PatchRevertableInterface
{
    private const ENTITY = 'customer_address';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly CustomerSetupFactory $customerSetupFactory
    ) {}

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $this->addIsBusinessAccount($customerSetup);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    private function addIsBusinessAccount(CustomerSetup $customerSetup): void
    {
        $customerSetup->addAttribute(self::ENTITY, 'is_business_account', [
            'type'          => 'int',
            'label'         => 'Użyj danych firmy',
            'input'         => 'boolean',
            'required'      => false,
            'default'       => '0',
            'visible'       => true,
            'user_defined'  => true,
            'sort_order'    => 10,
            'position'      => 10,
            'system'        => false,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(self::ENTITY, 'is_business_account');
        $attribute->addData([
            'used_in_forms' => [
                'customer_register_address',
                'customer_address_edit',
                'adminhtml_customer_address',
                'checkout_onepage_billing',
                'checkout_onepage_shipping',
            ],
        ]);
        $attribute->save();
    }

    public function revert(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->removeAttribute(self::ENTITY, 'is_business_account');

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [AddCustomerAttributes::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
