<?php
declare(strict_types=1);

namespace BlueMedia\BluePaymentGraphQl\Model\Resolver;

use BlueMedia\BluePayment\Model\ConfigProvider;
use BlueMedia\BluePayment\Model\Gateways;
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
    )
    {
        $gateways = [];

        foreach ($this->configProvider->getActiveGateways($args['value'], $args['currency']) as $gateway) {
            if ($this->available($gateway)) {
                $gateways[] = $this->prepareGateway($gateway);
            }
        }

        $this->configProvider->sortGateways($gateways);

        return $gateways;
    }

    protected function available(Gateways $gateway)
    {
        return $gateway->getGatewayId() != ConfigProvider::AUTOPAY_GATEWAY_ID
            && !$gateway->getIsSeparatedMethod();
    }

    /**
     * @param Gateways $gateway
     * @param array $gateways
     * @return array
     */
    protected function prepareGateway(Gateways $gateway): array
    {
        return [
            'gateway_id' => $gateway->getGatewayId(),
            'name' => $gateway->getGatewayName(),
            'bank' => $gateway->getBankName(),
            'description' => $gateway->getGatewayDescription(),
            'sort_order' => $gateway->getGatewaySortOrder(),
            'type' => $gateway->getGatewayType(),
            'logo_url' => (bool)$gateway->getUseOwnLogo() ? $gateway->getGatewayLogoPath() : $gateway->getGatewayLogoUrl(),
            'is_separated_method' => $gateway->getIsSeparatedMethod()
        ];
    }
}
