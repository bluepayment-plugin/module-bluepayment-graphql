<?php
declare(strict_types=1);

namespace BlueMedia\BluePaymentGraphQl\Model\Resolver;

use BlueMedia\BluePayment\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\Order;
use Magento\SalesGraphQl\Model\Order\OrderAddress;
use Magento\SalesGraphQl\Model\Order\OrderPayments;
use Magento\Store\Model\ScopeInterface;

class BluePaymentOrder implements ResolverInterface
{
    /** @var Order */
    protected $order;

    /** @var OrderAddress */
    protected $orderAddress;

    /** @var OrderPayments */
    protected $orderPayments;

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var Data */
    protected $helper;

    public function __construct(
        Order $order,
        OrderAddress $orderAddress,
        OrderPayments $orderPayments,
        ScopeConfigInterface $scopeConfig,
        Data $helper
    ) {
        $this->order = $order;
        $this->orderAddress = $orderAddress;
        $this->orderPayments = $orderPayments;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
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
        $orderNumber = $args['order_number'];
        $hash = $args['hash'];

        /** @var Order $order */
        $order = $this->order->loadByIncrementId($orderNumber);

        /** @var Order\Payment $payment */
        $payment = $order->getPayment();

        $currency = $order->getOrderCurrencyCode();

        $serviceId = $this->scopeConfig->getValue(
            'payment/bluepayment/' . strtolower($currency) . '/service_id',
            ScopeInterface::SCOPE_STORE
        );
        $sharedKey = $this->scopeConfig->getValue(
            'payment/bluepayment/' . strtolower($currency) . '/shared_key',
            ScopeInterface::SCOPE_STORE
        );

        $hashData = [$serviceId, $order->getIncrementId(), $sharedKey];

        if ($this->helper->generateAndReturnHash($hashData) !== $hash) {
            throw new GraphQlAuthenticationException(__('Invalid hash.'));
        }

        return [
            'created_at' => $order->getCreatedAt(),
            'grand_total' => $order->getGrandTotal(),
            'id' => base64_encode($order->getEntityId()),
            'increment_id' => $order->getIncrementId(),
            'number' => $order->getIncrementId(),
            'order_date' => $order->getCreatedAt(),
            'order_number' => $order->getIncrementId(),
            'status' => $order->getStatusLabel(),
            'invoices' => $order->getInvoiceCollection(),
            'shipping_method' => $order->getShippingDescription(),
            'shipping_address' => $this->orderAddress->getOrderShippingAddress($order),
            'billing_address' => $this->orderAddress->getOrderBillingAddress($order),
            'payment_methods' => $this->orderPayments->getOrderPaymentMethod($order),
            'model' => $order,
            'bluepayment_state' => $payment->getAdditionalInformation('bluepayment_state')
        ];
    }
}
