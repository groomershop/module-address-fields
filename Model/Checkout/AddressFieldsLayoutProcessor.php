<?php
declare(strict_types=1);

namespace GroomerShop\AddressFields\Model\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;

class AddressFieldsLayoutProcessor implements LayoutProcessorInterface
{
    public function __construct(
        private readonly ArrayManager $arrayManager
    ) {}

    public function process($jsLayout): array
    {
        $jsLayout = $this->addToShippingAddress($jsLayout);
        $jsLayout = $this->addToBillingPerPaymentMethod($jsLayout);
        $jsLayout = $this->addToBillingAfterMethods($jsLayout);

        return $jsLayout;
    }

    private function addToShippingAddress(array $jsLayout): array
    {
        $path = 'components/checkout/children/steps/children/shipping-step/children'
            . '/shippingAddress/children/shipping-address-fieldset/children';

        if (!$this->arrayManager->exists($path, $jsLayout)) {
            return $jsLayout;
        }

        return $this->arrayManager->set(
            $path . '/is_business_account',
            $jsLayout,
            $this->buildFieldConfig('shippingAddress')
        );
    }

    private function addToBillingPerPaymentMethod(array $jsLayout): array
    {
        $paymentsListPath = 'components/checkout/children/steps/children/billing-step'
            . '/children/payment/children/payments-list/children';

        foreach (array_keys($this->arrayManager->get($paymentsListPath, $jsLayout, [])) as $paymentKey) {
            $fieldsPath = $paymentsListPath . '/' . $paymentKey . '/children/form-fields/children';

            if (!$this->arrayManager->exists($fieldsPath, $jsLayout)) {
                continue;
            }

            // payment key format: "checkmo-form" → scope: "billingAddresscheckmo"
            $scope = 'billingAddress' . str_replace('-form', '', $paymentKey);
            $jsLayout = $this->arrayManager->set(
                $fieldsPath . '/is_business_account',
                $jsLayout,
                $this->buildFieldConfig($scope)
            );
        }

        return $jsLayout;
    }

    private function addToBillingAfterMethods(array $jsLayout): array
    {
        $path = 'components/checkout/children/steps/children/billing-step'
            . '/children/payment/children/afterMethods/children/billing-address-form'
            . '/children/form-fields/children';

        if (!$this->arrayManager->exists($path, $jsLayout)) {
            return $jsLayout;
        }

        return $this->arrayManager->set(
            $path . '/is_business_account',
            $jsLayout,
            $this->buildFieldConfig('billingAddressshared')
        );
    }

    private function buildFieldConfig(string $scope): array
    {
        return [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config'    => [
                'customScope' => $scope . '.custom_attributes',
                'template'    => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/checkbox',
            ],
            'provider'  => 'checkoutProvider',
            'dataScope' => $scope . '.custom_attributes.is_business_account',
            'label'     => (string) __('Order as company'),
            'sortOrder' => 90,
            'visible'   => true,
            'value'     => '0',
            'valueMap'  => ['true' => '1', 'false' => '0'],
        ];
    }
}
