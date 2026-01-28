<?php
declare(strict_types=1);

namespace BlueMedia\BluePaymentGraphQl\Plugin\Model\Resolver;

use BlueMedia\BluePayment\Model\ConfigProvider;
use BlueMedia\BluePayment\Model\Gateway;
use BlueMedia\BluePayment\Model\Payment;
use Closure;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Resolver\SelectedPaymentMethod;

class SelectedPaymentMethodPlugin
{
    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @param ConfigProvider $configProvider
     */
    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * Return gateway name as payment method title for bluepayment.
     *
     * @param SelectedPaymentMethod $subject
     * @param Closure $proceed
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function aroundResolve(
        SelectedPaymentMethod $subject,
        Closure $proceed,
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        /** @var Quote $cart */
        $cart = $value['model'];

        $payment = $cart->getPayment();
        if (!$payment) {
            return [];
        }

        if ($payment->hasAdditionalInformation('gateway_id') && $payment->getMethodInstance() instanceof Payment) {
            $gatewayId = (int) $payment->getAdditionalInformation('gateway_id');
            $gateways = $this->configProvider->getActiveGateways(
                $cart->getQuoteCurrencyCode(),
                (float) $cart->getGrandTotal()
            );

            foreach ($gateways as $gateway) {
                /** @var Gateway $gateway */
                if ($gateway->getGatewayId() === $gatewayId) {
                    return [
                        'code' => $payment->getMethod() ?? '',
                        'title' => $gateway->getName(),
                        'purchase_order_number' => $payment->getPoNumber(),
                    ];
                }
            }
        }

        return $proceed($field, $context, $info, $value, $args);
    }
}
