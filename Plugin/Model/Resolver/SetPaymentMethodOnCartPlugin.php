<?php
declare(strict_types=1);

namespace BlueMedia\BluePaymentGraphQl\Plugin\Model\Resolver;

use BlueMedia\BluePayment\Model\ConfigProvider;
use BlueMedia\BluePayment\Model\Payment;
use Magento\Framework\Exception\NoSuchEntityException;
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

    /**
     * AvailablePaymentMethodsPlugin constructor.
     *
     * @param ConfigProvider $configProvider
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        ConfigProvider $configProvider,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->configProvider = $configProvider;
        $this->priceCurrency = $priceCurrency;
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
     * @throws GraphQlInputException
     */
    public function beforeResolve(
        SetPaymentMethodOnCart $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        $code = $args['input']['payment_method']['code'];
        if (false !== strpos($code, Payment::SEPARATED_PREFIX_CODE)) {
            $args['input']['payment_method']['bluepayment']['gateway_id'] = str_replace(
                Payment::SEPARATED_PREFIX_CODE,
                '',
                $code
            );

            $code = 'bluepayment';
            $args['input']['payment_method']['code'] = $code;
        }

        if ($code === 'bluepayment') {
            if (isset($args['input']['payment_method']['bluepayment']['gateway_id'])) {
                $gatewayId = (int) $args['input']['payment_method']['bluepayment']['gateway_id'];

                if (! $this->validateGatewayId($gatewayId)) {
                    throw new GraphQlInputException(__('Selected "gateway_id" is not active or does not exists.'));
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

    /**
     * Get current currency code.
     *
     * @return string|null
     */
    protected function getCurrencyCode(): ?string
    {
        return $this->priceCurrency->getCurrency()
            ->getCurrencyCode();
    }

    /**
     * Returns whether gateway id is valid and available.
     *
     * @param int $gatewayId
     * @return bool
     * @throws NoSuchEntityException
     */
    protected function validateGatewayId(int $gatewayId): bool
    {
        $availablePaymentMethods = $this->configProvider->getActiveGateways(
            $this->getCurrencyCode()
        );

        foreach ($availablePaymentMethods as $method) {
            if ((int) $method->getGatewayId() === $gatewayId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether back url is valid.
     *
     * @param string $backUrl
     * @return bool
     */
    protected function validateBackUrl(string $backUrl): bool
    {
        if (!$this->strStartsWith($backUrl, 'https://') && !$this->strStartsWith($backUrl, 'http://')) {
            return false;
        }

        return (bool) filter_var($backUrl, FILTER_VALIDATE_URL);
    }

    /**
     * Returns whether string starts with given prefix.
     *
     * @param string $str
     * @param string $needle
     * @return bool
     */
    protected function strStartsWith(string $str, string $needle): bool
    {
        return $needle === "" || 0 === strncmp($str, $needle, \strlen($needle));
    }
}
