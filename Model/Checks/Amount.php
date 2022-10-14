<?php
declare(strict_types=1);

namespace BlueMedia\BluePaymentGraphQl\Model\Checks;

use BlueMedia\BluePayment\Model\Gateway;
use BlueMedia\BluePayment\Model\Payment;
use BlueMedia\BluePaymentGraphQl\Model\ConfigProvider;
use Magento\Payment\Model\Checks\SpecificationInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

class Amount implements SpecificationInterface
{
    /**
     * Check whether payment method is applicable to quote.
     *
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     */
    public function isApplicable(MethodInterface $paymentMethod, Quote $quote): bool
    {
        $code = $paymentMethod->getCode();

        if (false === strpos($code, Payment::SEPARATED_PREFIX_CODE)) {
            return true;
        }

        if (! $paymentMethod->getGatewayModel()) {
            return true;
        }

        /** @var Gateway $gateway */
        $gateway = $paymentMethod->getGatewayModel();

        if (in_array((int) $gateway->getGatewayId(), ConfigProvider::UNAVAILABLE_GATEWAYS, true)) {
            return false;
        }

        // For null - we don't need to check amounts.
        $minAmount = $paymentMethod->getGatewayModel()->getMinAmount() ?? false;
        $maxAmount = $paymentMethod->getGatewayModel()->getMaxAmount() ?? false;

        $total = (float) $quote->getGrandTotal();

        if ($minAmount && $total < (float) $minAmount) {
            return false;
        }

        if ($maxAmount && $total > (float) $maxAmount) {
            return false;
        }

        return true;
    }
}
