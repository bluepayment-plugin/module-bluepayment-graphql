<?php

namespace BlueMedia\BluePaymentGraphQl\Plugin\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesGraphQl\Model\Order\OrderPayments;

class SetTitleToBluepaymentMethod
{
    /**
     * @param \Magento\Sales\Model\Order\Payment $subject
     * @param array $result
     * @return string
     */
    public function aroundGetOrderPaymentMethod(OrderPayments $subject, \Closure $proceed, OrderInterface $orderModel)
    {
        $result = $proceed($orderModel);

        if ($result[0]['type'] === 'bluepayment') {
            $channel = $orderModel->getData('payment_channel');

            if ($channel) {
                $result[0]['name'] = $orderModel->getData('payment_channel');
            }
        }

        return $result;
    }
}
