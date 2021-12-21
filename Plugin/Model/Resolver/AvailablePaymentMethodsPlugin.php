<?php

namespace BlueMedia\BluePaymentGraphQl\Plugin\Model\Resolver;

use BlueMedia\BluePayment\Model\ConfigProvider;
use Magento\QuoteGraphQl\Model\Resolver\AvailablePaymentMethods;

class AvailablePaymentMethodsPlugin
{
    /**
     * @param AvailablePaymentMethods $subject
     * @param array $list
     * @return array
     */
    public function afterResolve(AvailablePaymentMethods $subject, array $list)
    {
        foreach ($list as $i => $array) {
            if (strpos($array['code'], 'bluepayment_') !== false) {
                $list[$i]['gateway_id'] = str_replace('bluepayment_', '', $array['code']);
            }
        }

        return $list;
    }
}
