<?php
declare(strict_types=1);

namespace BlueMedia\BluePaymentGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Authorization\Model\UserContextInterface;

class RedirectUrl implements ResolverInterface
{
    /**
     * @var Order
     */
    protected $order;

    private $maskedQuoteIdToQuoteId;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @param Order $order
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Order $order,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        StoreManagerInterface $storeManager
    ) {
        $this->order = $order;
        $this->storeManager = $storeManager;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
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
        $storeId = (int) $this->storeManager->getStore()->getId();

        /** @var Order $order */
        $order = $this->order->loadByIncrementIdAndStoreId($orderNumber, $storeId);
        if (!$order->getId() || !$this->canAccessOrder($order, $context, $args['cart_id'] ?? null)) {
            throw new GraphQlAuthorizationException(__("The current user isn't authorized."));
        }

        $payment = $order->getPayment();
        return $payment ? ($payment->getAdditionalInformation('bluepayment_redirect_url') ?? '') : '';
    }

    private function canAccessOrder(Order $order, $context, $cartId): bool
    {
        if ($context->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
            $customerId = (int) $context->getUserId();
            return $customerId > 0 && $customerId === (int) $order->getCustomerId();
        }

        if (!$order->getCustomerIsGuest() || $order->getCustomerId()
            || !is_string($cartId) || trim($cartId) === '' || (int) $order->getQuoteId() <= 0
        ) {
            return false;
        }

        try {
            // Use the masked cart mapping even after placing the order deactivates the quote.
            return $this->maskedQuoteIdToQuoteId->execute($cartId) === (int) $order->getQuoteId();
        } catch (NoSuchEntityException $exception) {
            return false;
        }
    }
}
