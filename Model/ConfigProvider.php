<?php
declare(strict_types=1);

namespace BlueMedia\BluePaymentGraphQl\Model;

use BlueMedia\BluePayment\Model\ConfigProvider as BluePaymentConfigProvider;

class ConfigProvider
{
    public const UNAVAILABLE_GATEWAYS = [
        BluePaymentConfigProvider::ONECLICK_GATEWAY_ID,
        BluePaymentConfigProvider::HUB_GATEWAY_ID,
    ];
}
