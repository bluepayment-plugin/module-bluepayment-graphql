<?php

namespace BlueMedia\BluePaymentGraphQl\Plugin\Model\Resolver;

use BlueMedia\BluePayment\Model\ConfigProvider;
use Magento\QuoteGraphQl\Model\Resolver\AvailablePaymentMethods;

class AvailablePaymentMethodsPlugin
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
     * @param AvailablePaymentMethods $subject
     * @param array $list
     * @return array
     */
    public function afterResolve(AvailablePaymentMethods $subject, array $list)
    {
        if ($this->configProvider->getPaymentConfig()['bluePaymentSeparated']) {
            foreach ($this->configProvider->getPaymentConfig()['bluePaymentSeparated'] as $separated) {
                $code = 'bluepayment_'.$separated['gateway_id'];

                $list[$code] = [
                    'title' => $separated['name'],
                    'code' => $code
                ];
            }
        }

        return $list;
    }
}
