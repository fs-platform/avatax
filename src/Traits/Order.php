<?php

namespace Smbear\Avatax\Traits;

use Smbear\Avatax\Enums\AvataxEnums;
use Smbear\Avatax\Exceptions\ParamsException;

trait Order
{
    public $order = [];

    public $lines = [];

    /**
     * @Notes:设置order信息
     *
     * @param array $parameters
     * @return object
     * @Author: smile
     * @Date: 2021/5/31
     * @Time: 18:27
     */
    public function setOrder(array $parameters) : object
    {
        $parameters = avatax_format_params($parameters);

        $this->order = [
            'code'                  => $parameters['documentCode'] ?? '',
            'customerCode'          => $parameters['customerCode'] ?? AvataxEnums::CUSTOMER_CODE,
            'entityUseCode'         => $parameters['customerCode'] ?? AvataxEnums::CUSTOMER_CODE,
            'currencyCode'          => $parameters['currencyCode'] ?? AvataxEnums::CURRENCY_CODE,
            'exchangeRate'          => $parameters['exchangeRate'] ?? AvataxEnums::EXCHANGE_RATE,
            'purchaseOrderNo'       => $parameters['purchaseOrderNo'] ?? '',
            'description'           => isset($parameters['description']) ? mb_substr($parameters['description'],0,60)  : '',
            'salespersonCode'       => $parameters['salespersonCode'] ?? AvataxEnums::ADMIN_ID,
        ];

        return $this;
    }

    /**
     * @Notes:设置lines信息
     *
     * @param $parameters
     * @return object
     * @Author: smile
     * @Date: 2021/6/1
     * @Time: 10:10
     */
    public function setLines($parameters) : object
    {
        $parameters = avatax_format_params($parameters);

        foreach ($parameters as &$lines){
            foreach ($lines as $key => $line){
//                if (strtolower($line['itemCode'] ?? '') == AvataxEnums::SHIPPING){
//                    $taxCode = config('avatax.shippingTaxCode');
//                } else {
//                    $taxCode = config('avatax.productsTaxCode');
//                }
                $itemCode = strtolower($line['itemCode'] ?? '');
                switch ($itemCode) {
                    case AvataxEnums::SHIPPING:
                        $taxCode = config('avatax.shippingTaxCode');
                        break;
                    case AvataxEnums::US_CO://美国CO州新增税种
                        $taxCode = config('avatax.usCoTaxCode');
                        break;
                    default:
                        $taxCode = config('avatax.productsTaxCode');
                        break;
                }
                $lines[$key] = [
                    'amount'   => (float) $line['amount'] ?? 0 * (int) $line['quantity'] ?? 1,
                    'quantity' => $line['quantity'] ?? 1,
                    'itemCode' => $line['itemCode'] ?? '',
                    'taxCode'  => $taxCode,
                    'number'   => $key +1
                ];
            }
        }

        $this->lines = $parameters;

        return $this;
    }

    /**
     * @Notes:获取order
     *
     * @return array
     * @Author: smile
     * @Date: 2021/5/27
     * @Time: 18:11
     * @throws ParamsException
     */
    public function getOrder() : array
    {
        if (empty($this->order)){
            throw new ParamsException('order 参数异常，请先通过 setOrder 设置参数');
        }

        return $this->order;
    }

    /**
     * @Notes:获取lines
     *
     * @return array
     * @Author: smile
     * @Date: 2021/6/1
     * @Time: 10:10
     * @throws ParamsException
     */
    public function getLines() : array
    {
        if (empty($this->lines)){
            throw new ParamsException('lines 参数异常，请先通过 setLines 设置参数');
        }

        return $this->lines;
    }
}
