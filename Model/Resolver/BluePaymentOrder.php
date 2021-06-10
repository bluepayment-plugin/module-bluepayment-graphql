<?php
declare(strict_types=1);

namespace BlueMedia\BluePaymentGraphQl\Model\Resolver;

use BlueMedia\BluePayment\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;

class BluePaymentOrder implements ResolverInterface
{
    /** @var Order */
    protected $order;

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var Data */
    protected $helper;

    public function __construct(
        Order $order,
        ScopeConfigInterface $scopeConfig,
        Data $helper
    ) {
        $this->order = $order;
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
        $websiteCode = $order->getStore()->getWebsite()->getCode();

        $serviceId = $this->scopeConfig->getValue(
            'payment/bluepayment/' . strtolower($currency) . '/service_id',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteCode
        );
        $sharedKey = $this->scopeConfig->getValue(
            'payment/bluepayment/' . strtolower($currency) . '/shared_key',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteCode
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
            'shipping_address' => $this->getOrderShippingAddress($order),
            'billing_address' => $this->getOrderBillingAddress($order),
            'payment_methods' => $this->getOrderPaymentMethod($order),
            'model' => $order,
            'bluepayment_state' => $payment->getAdditionalInformation('bluepayment_state'),
        ];
    }

    /**
     * Get the order Shipping address
     *
     * @param OrderInterface $order
     *
     * @return array|null
     */
    public function getOrderShippingAddress(OrderInterface $order) {
        $shippingAddress = null;
        if ($order->getShippingAddress()) {
            $shippingAddress = $this->formatAddressData($order->getShippingAddress());
        }
        return $shippingAddress;
    }

    /**
     * Get the order billing address
     *
     * @param OrderInterface $order
     *
     * @return array|null
     */
    public function getOrderBillingAddress(OrderInterface $order) {
        $billingAddress = null;
        if ($order->getBillingAddress()) {
            $billingAddress = $this->formatAddressData($order->getBillingAddress());
        }
        return $billingAddress;
    }

    /**
     * Customer Order address data formatter
     *
     * @param OrderAddressInterface $orderAddress
     * @return array
     */
    private function formatAddressData(OrderAddressInterface $orderAddress) {
        return
            [
                'firstname' => $orderAddress->getFirstname(),
                'lastname' => $orderAddress->getLastname(),
                'middlename' => $orderAddress->getMiddlename(),
                'postcode' => $orderAddress->getPostcode(),
                'prefix' => $orderAddress->getPrefix(),
                'suffix' => $orderAddress->getSuffix(),
                'street' => $orderAddress->getStreet(),
                'country_code' => $orderAddress->getCountryId(),
                'city' => $orderAddress->getCity(),
                'company' => $orderAddress->getCompany(),
                'fax' => $orderAddress->getFax(),
                'telephone' => $orderAddress->getTelephone(),
                'vat_id' => $orderAddress->getVatId(),
                'region_id' => $orderAddress->getRegionId(),
                'region' => $orderAddress->getRegion(),
            ];
    }

    /**
     * Get the order payment method
     *
     * @param OrderInterface $orderModel
     * @return array
     */
    public function getOrderPaymentMethod(OrderInterface $orderModel)
    {
        $orderPayment = $orderModel->getPayment();
        return [[
            'name' => $orderPayment->getAdditionalInformation()['method_title'] ?? '',
            'type' => $orderPayment->getMethod(),
            'additional_data' => []
        ]];
    }
}
