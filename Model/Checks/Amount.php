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

        if (!str_contains($code, Payment::SEPARATED_PREFIX_CODE)) {
            return true;
        }

        if (!$paymentMethod->getGatewayModel()) {
            return true;
        }

        /** @var Gateway $gateway */
        $gateway = $paymentMethod->getGatewayModel();

        if (in_array((int) $gateway->getGatewayId(), ConfigProvider::UNAVAILABLE_GATEWAYS, true)) {
            return false;
        }

        $total = (float) $quote->getGrandTotal();
        $minAmount = $gateway->getMinAmount();
        $maxAmount = $gateway->getMaxAmount();

        if ($minAmount !== null && $total < $minAmount) {
            return false;
        }

        if ($maxAmount !== null && $total > $maxAmount) {
            return false;
        }

        return true;
    }
}
