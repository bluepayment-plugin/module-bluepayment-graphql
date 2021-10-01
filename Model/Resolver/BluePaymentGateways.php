<?php
declare(strict_types=1);

namespace BlueMedia\BluePaymentGraphQl\Model\Resolver;

use BlueMedia\BluePayment\Model\ConfigProvider;
use BlueMedia\BluePayment\Model\Gateway;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class BluePaymentGateways implements ResolverInterface
{
    /** @var ConfigProvider */
    protected $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $gateways = [];

        if ($this->configProvider->isGatewaySelectionEnabled()) {
            foreach ($this->configProvider->getActiveGateways($args['value'], $args['currency']) as $gateway) {
                if ($this->available($gateway)) {
                    $gateways[] = $this->prepareGateway($gateway);
                }
            }

            $this->configProvider->sortGateways($gateways);
        }

        return $gateways;
    }

    protected function available(Gateway $gateway)
    {
        return $gateway->getGatewayId() != ConfigProvider::AUTOPAY_GATEWAY_ID
            && !$gateway->isSeparatedMethod();
    }

    /**
     * @param Gateway $gateway
     * @return array
     */
    protected function prepareGateway(Gateway $gateway): array
    {
        return [
            'gateway_id' => $gateway->getGatewayId(),
            'name' => $gateway->getName(),
            'bank' => $gateway->getBankName(),
            'description' => $gateway->getDescription(),
            'sort_order' => $gateway->getSortOrder(),
            'type' => $gateway->getType(),
            'logo_url' => $gateway->getUseOwnLogo() ? $gateway->getLogoPath() : $gateway->getLogoUrl(),
            'is_separated_method' => $gateway->isSeparatedMethod()
        ];
    }
}
