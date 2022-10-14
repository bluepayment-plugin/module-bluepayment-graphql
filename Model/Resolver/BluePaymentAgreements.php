<?php
declare(strict_types=1);

namespace BlueMedia\BluePaymentGraphQl\Model\Resolver;

use BlueMedia\BluePayment\Helper\Webapi;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class BluePaymentAgreements implements ResolverInterface
{
    /** @var Webapi */
    protected $webapi;

    public function __construct(Webapi $webapi)
    {
        $this->webapi = $webapi;
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
        if ($args['currency'] !== 'PLN') {
            // Currency other than PLN are not supported.
            return [];
        }

        $result = $this->webapi->agreements($args['gateway_id'], $args['currency'], $args['locale']);
        if (!$result || !is_array($result) || !isset($result['regulationList'])) {
            return [];
        }
        return array_map(function ($agreement) {
            return $this->prepareAgreement((array) $agreement);
        }, $result['regulationList']);
    }

    /**
     * Prepare agreement structure.
     *
     * @param array $agreement
     * @return array
     */
    protected function prepareAgreement(array $agreement): array
    {
        return [
            'regulation_id' => $agreement['regulationID'],
            'type' => $agreement['type'],
            'url' => $agreement['url'],
            'label_list' => array_map(function ($label) {
                return $this->prepareLabel((array) $label);
            }, $agreement['labelList']),
        ];
    }

    /**
     * Prepare label structure.
     *
     * @param array $label
     * @return array
     */
    protected function prepareLabel(array $label): array
    {
        return [
            'label_id' => $label['labelID'],
            'label' => $label['inputLabel'],
            'placement' => $label['placement'],
            'show_checkbox' => $label['showCheckbox'],
            'checkbox_required' => $label['checkboxRequired'],
        ];
    }
}
