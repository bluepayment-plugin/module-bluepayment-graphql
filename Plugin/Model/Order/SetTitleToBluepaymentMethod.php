<?php
declare(strict_types=1);

namespace BlueMedia\BluePaymentGraphQl\Plugin\Model\Order;

use BlueMedia\BluePayment\Model\Payment;
use Closure;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesGraphQl\Model\Order\OrderPayments;

class SetTitleToBluepaymentMethod
{
    /**
     * Replace bluepayment method name with actual payment channel name.
     *
     * @param OrderPayments $subject
     * @param Closure $proceed
     * @param OrderInterface $orderModel
     * @return array
     */
    public function aroundGetOrderPaymentMethod(
        OrderPayments $subject,
        Closure $proceed,
        OrderInterface $orderModel
    ): array {
        $result = $proceed($orderModel);

        if (!empty($result[0]['type']) && $result[0]['type'] === Payment::METHOD_CODE) {
            $channel = $orderModel->getData('payment_channel');

            if ($channel) {
                $result[0]['name'] = $channel;
            }
        }

        return $result;
    }
}
