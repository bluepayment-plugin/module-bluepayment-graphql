<?php

namespace BlueMedia\BluePaymentGraphQl\Plugin\Model\Resolver;

use BlueMedia\BluePayment\Model\ConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\QuoteGraphQl\Model\Resolver\SetPaymentMethodOnCart;

class SetPaymentMethodOnCartPlugin
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /** @var PriceCurrencyInterface */
    private $priceCurrency;

    /** @var CheckoutSession */
    private $checkoutSession;

    /**
     * AvailablePaymentMethodsPlugin constructor.
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ConfigProvider $configProvider,
        PriceCurrencyInterface $priceCurrency,
        CheckoutSession $checkoutSession
    ) {
        $this->configProvider = $configProvider;
        $this->priceCurrency = $priceCurrency;
        $this->checkoutSession = $checkoutSession;
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

            $code = 'bluepayment';
            $args['input']['payment_method']['code'] = $code;
        }

        if ($code === 'bluepayment') {
            if (isset($args['input']['payment_method']['bluepayment']['gateway_id'])) {
                $gatewayId = $args['input']['payment_method']['bluepayment']['gateway_id'];

                if (! $this->validateGatewayId($gatewayId)) {
                    throw new GraphQlInputException(__('Selected "gateway_id" is not active or does not exists.'));
                }

                if ($gatewayId === ConfigProvider::CREDIT_GATEWAY_ID && ! $this->validateCreditGateway()) {
                    throw new GraphQlInputException(__('This gateway is not available for amount.'));
                }
            }

            if (isset($args['input']['payment_method']['bluepayment']['back_url'])) {
                $backUrl = $args['input']['payment_method']['bluepayment']['back_url'];
                if (! $this->validateBackUrl($backUrl)) {
                    throw new GraphQlInputException(__('Back URL is not valid.'));
                }
            }
        }


        return [$field, $context, $info, $value, $args];
    }

    protected function getCurrencyCode()
    {
        return $this->priceCurrency->getCurrency()->getCurrencyCode();
    }

    protected function getAmount()
    {
        return $this->checkoutSession->getQuote()->getGrandTotal();
    }

    protected function validateGatewayId($gatewayId)
    {
        $availablePaymentMethods = $this->configProvider->getActiveGateways(
            $this->getAmount(),
            $this->getCurrencyCode()
        );

        foreach ($availablePaymentMethods as $method) {
            if ($method->getGatewayId() == $gatewayId) {
                return true;
            }
        }

        return false;
    }

    protected function validateCreditGateway()
    {
        $amount = $this->getAmount();

        if ($amount < 100 || $amount > 2500) {
            return false;
        }

        return true;
    }

    protected function validateBackUrl($backUrl)
    {
        if (!$this->strStartsWith($backUrl, 'https://') && !$this->strStartsWith($backUrl, 'http://')) {
            return false;
        }

        return filter_var($backUrl, FILTER_VALIDATE_URL);
    }

    protected function strStartsWith($str, $needle)
    {
        return $needle === "" || 0 === strncmp($str, $needle, \strlen($needle));
    }
}
