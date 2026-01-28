<?php
declare(strict_types=1);

namespace BlueMedia\BluePaymentGraphQl\Plugin\Model\Resolver;

use BlueMedia\BluePayment\Model\Payment;
use Magento\QuoteGraphQl\Model\Resolver\AvailablePaymentMethods;

class AvailablePaymentMethodsPlugin
{
    /**
     * Add gateway_id to bluepayment methods in available payment methods list.
     *
     * @param AvailablePaymentMethods $subject
     * @param array $list
     * @return array
     */
    public function afterResolve(AvailablePaymentMethods $subject, array $list): array
    {
        foreach ($list as $i => $array) {
            if (str_contains($array['code'], Payment::SEPARATED_PREFIX_CODE)) {
                $list[$i]['gateway_id'] = str_replace(Payment::SEPARATED_PREFIX_CODE, '', $array['code']);
            }
        }

        return $list;
    }
}
