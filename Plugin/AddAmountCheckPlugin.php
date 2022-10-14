<?php
declare(strict_types=1);

namespace BlueMedia\BluePaymentGraphQl\Plugin;

use Magento\Payment\Model\Checks\Composite;
use Magento\Payment\Model\Checks\SpecificationFactory;

class AddAmountCheckPlugin
{
    /**
     * Add BM amount check to list of checks.
     *
     * @param SpecificationFactory $subject
     * @param array $data
     * @return array
     */
    public function beforeCreate(SpecificationFactory $subject, array $data): array
    {
        return [
            array_merge(
                $data,
                ['bm_amount']
            )
        ];
    }
}
