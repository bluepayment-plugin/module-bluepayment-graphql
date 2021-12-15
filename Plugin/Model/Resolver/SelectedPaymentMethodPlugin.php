<?php

namespace BlueMedia\BluePaymentGraphQl\Plugin\Model\Resolver;

use BlueMedia\BluePayment\Model\ConfigProvider;
use BlueMedia\BluePayment\Model\Gateway;
use BlueMedia\BluePayment\Model\Payment;
use Closure;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Resolver\SelectedPaymentMethod;

class SelectedPaymentMethodPlugin
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * AvailablePaymentMethodsPlugin constructor.
     * @param ConfigProvider $configProvider
     */
    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }


    /**
     * @param SelectedPaymentMethod $subject
     * @param Closure $proceed
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function aroundResolve(SelectedPaymentMethod $subject, Closure $proceed, Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var Quote $cart */
        $cart = $value['model'];

        $payment = $cart->getPayment();
        if (!$payment) {
            return [];
        }

        if ($payment->hasAdditionalInformation('gateway_id') && $payment->getMethodInstance() instanceof Payment) {
            $gateway_id = $payment->getAdditionalInformation('gateway_id');
            $gateways = $this->configProvider->getActiveGateways($cart->getGrandTotal(), $cart->getQuoteCurrencyCode());
            foreach ($gateways as $gateway) {
                /** @var Gateway $gateway */

                if ($gateway->getGatewayId() == $gateway_id) {
                    return [
                        'code' => $payment->getMethod() ?? '',
                        'title' => $gateway->getGatewayName(),
                        'purchase_order_number' => $payment->getPoNumber(),
                    ];
                }
            }
        }

        return $proceed($field, $context, $info, $value, $args);
    }
}
