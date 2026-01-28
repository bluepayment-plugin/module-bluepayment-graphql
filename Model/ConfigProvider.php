<?php
declare(strict_types=1);

namespace BlueMedia\BluePaymentGraphQl\Model;

use BlueMedia\BluePayment\Model\ConfigProvider as BluePaymentConfigProvider;

class ConfigProvider
{
    /**
     * Gateways unavailable for GraphQL/headless usage.
     * ONECLICK requires customer session and saved cards management.
     */
    public const UNAVAILABLE_GATEWAYS = [
        BluePaymentConfigProvider::ONECLICK_GATEWAY_ID,
    ];
}
