<?php
declare(strict_types=1);

namespace BlueMedia\BluePaymentGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\Order;

class RedirectUrl implements ResolverInterface
{
    /**
     * @var Order
     */
    protected Order $order;

    /**
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $orderNumber = $args['order_number'];

        /** @var Order $order */
        $order = $this->order->loadByIncrementId($orderNumber);
        return $order->getPayment()->getAdditionalInformation('bluepayment_redirect_url') ?? '';
    }
}
