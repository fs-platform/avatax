<?php

namespace Smbear\Avatax\Services;

use Illuminate\Support\Carbon;
use Avalara\TransactionBuilder;
use Smbear\Avatax\Enums\AvaTaxEnums;

class AvataxTransService
{
    public $build;

    /**
     * @Notes:计算税费
     *
     * @param object $client
     * @param string $type
     * @param array $address
     * @param array $order
     * @param array $lines
     * @param array $fromAddress
     * @return mixed
     * @Author: smile
     * @Date: 2021/5/27
     * @Time: 17:31
     */
    public function transaction(object $client,string $type,array $address,array $order,array $lines,array $fromAddress): array
    {
        $result = [];

        foreach ($lines as $key => $line){
            $this->getBuild($client,$type,$order['customerCode'])
                ->shipToAddress($address)
                ->shipFromAddress($fromAddress)
                ->withLines($line)
                ->withMethod($order);

            $builder = $this->build;

            if ($type == 'SalesInvoice'){
                $builder = $builder->withCommit();
            }

            $result[$key] = $builder->createOrAdjust();
        }

        return $result;
    }

    /**
     * @Notes:建立模型
     *
     * @param object $client
     * @param string $type
     * @param int $customerCode
     * @return object
     * @Author: smile
     * @Date: 2021/5/27
     * @Time: 17:28
     */
    public function getBuild(object $client,string $type,int $customerCode = 0) : object
    {
        $this->build = new TransactionBuilder(
            $client,
            avatax_get_config_value('companyCode'),
            $type,
            $customerCode,
            Carbon::now(AvataxEnums::TZ)->format('Y-m-d')
        );

        return $this;
    }

    /**
     * @Notes:收获地址
     *
     * @param array $address
     * @return object
     * @Author: smile
     * @Date: 2021/5/27
     * @Time: 17:32
     */
    public function shipToAddress(array $address) : object
    {
        $this->build = $this->build->withAddress(
            'ShipTo',
            $address['line1'],
            $address['line2'],
            $address['line3'],
            $address['city'],
            $address['region'],
            $address['postalCode'],
            $address['country']
        );

        return $this;
    }

    /**
     * @Notes:发送地址
     *
     * @param array $fromAddress
     * @return object
     * @Author: smile
     * @Date: 2021/5/27
     * @Time: 17:33
     */
    public function shipFromAddress(array $fromAddress) : object
    {
        $this->build = $this->build->withAddress(
            'ShipFrom',
            $fromAddress['line1'],
            $fromAddress['line2'],
            $fromAddress['line3'],
            $fromAddress['city'],
            $fromAddress['region'],
            $fromAddress['postalCode'],
            $fromAddress['country']
        );

        return $this;
    }

    /**
     * @Notes:产品信息
     *
     * @param array $lines
     * @return object
     * @Author: smile
     * @Date: 2021/5/27
     * @Time: 17:35
     */
    public function withLines(array $lines) : object
    {
        foreach ($lines as $key => $line){
            if (isset($line['category']) && !empty($line['category'])) {
                // 计算关税
                $l = [
                    [
                        'number'      => $line['number'],
                        'quantity'    => $line['quantity'],
                        'amount'      => $line['amount'],
                        'taxCode'     => $line['taxCode'],
                        'itemCode'    => $line['itemCode'],
                        'description' => isset($line['description']) && !empty($line['description']) ? mb_substr($line['description'], 0, 60) : "",
                        'category'    => $line['category'],
                    ],
                ];
                $this->build->withLineItem($l);
            } else {
                $this->build->withLine(
                    $line['amount'],
                    $line['quantity'],
                    $line['itemCode'],
                    $line['taxCode'],
                    $line['number']
                );
    
                if (isset($line['description']) && !empty($line['description'])){
                    $this->build->withLineDescription(mb_substr($line['description'],0,60));
                }
            }
        }
        return $this;
    }

    /**
     * @Notes:关联数据
     *
     * @param array $order
     * @return object
     * @Author: smile
     * @Date: 2021/5/27
     * @Time: 17:38
     */
    public function withMethod(array $order) : object
    {
        $params = $this->getMethodParams();

        foreach ($params as $key => $value){
            $method = $value['method'];

            if ($value['type'] == 'string'){
                if (isset($order[$key]) && !empty($order[$key])){
                    $this->build = $this->build->$method($order[$key]);
                }
            } else {
                if (isset($order[$key])){
                    $this->build = $this->build->$method($order[$key]);
                }
            }
        }

        return $this;
    }

    /**
     * @Notes: 方法数据
     *
     * @return array
     * @Author: smile
     * @Date: 2021/5/27
     * @Time: 17:37
     */
    private function getMethodParams() : array
    {
        return [
            'code' => [
                'method' => 'withTransactionCode',
                'type'   => 'string'
            ],
            'entityUseCode' => [
                'method' => 'withEntityUseCode',
                'type'   => 'int'
            ],
            'currencyCode' => [
                'method'  => 'withCurrencyCode',
                'type'    => 'int'
            ],
            'exchangeRate' => [
                'method' => 'withExchangeRate',
                'type'   => 'float'
            ],
            'description' => [
                'method'  => 'withDescription',
                'type'    => 'string'
            ],
            'purchaseOrderNo' => [
                'method' => 'withPurchaseOrderNo',
                'type'   => 'string'
            ],
            'salespersonCode' => [
                'method'  => 'withSalespersonCode',
                'type'    => 'int'
            ]
        ];
    }
}