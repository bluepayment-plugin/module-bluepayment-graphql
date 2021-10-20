<?php

namespace BlueMedia\BluePaymentGraphQl\Plugin\Model\Resolver;

use BlueMedia\BluePayment\Model\ConfigProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\SetPaymentMethodOnCart;

class SetPaymentMethodOnCartPlugin
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
     * @param  SetPaymentMethodOnCart  $subject
     * @param  Field  $field
     * @param $context
     * @param  ResolveInfo  $info
     * @param  array|null  $value
     * @param  array|null  $args
     *
     * @return array
     */
    public function beforeResolve(
        SetPaymentMethodOnCart $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $code = $args['input']['payment_method']['code'];
        if (false !== strpos($code, 'bluepayment_')) {
            $args['input']['payment_method']['bluepayment']['gateway_id'] = str_replace('bluepayment_', '', $code);
            $args['input']['payment_method']['code'] = 'bluepayment';
        }

        return [$field, $context, $info, $value, $args];
    }
}
