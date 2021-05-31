# 说明

* 依赖avatax官方扩展包
* 采用psr-4的标准
* 单元测试覆盖基本功能

# 安装配置

安装composer包

```
composer require smbear/avatax
```

发布配置文件

```
php artisan vendor:publish --provider=Smbear\Avatax\AvataxServiceProvider
```

迁移数据表

```
php artisan migrate
```

配置日志channel(config/logging)

```
'avatax' => [
    'driver' => 'daily',
    'path' => storage_path('logs/avatax/avatax.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => 14,
],
```

# 使用方式（门面/契约）

```
use Smbear\Avatax\Facades\Avatax;

$result = Avatax::setAddress([
    'line1' => '380 Centerpoint Blvd ',
    'city' => 'New Castle',
    'country' => 'US',
    'postalCode' => '1934720',
    'region' => 'DE'
])->setOrder([
    'documentCode'     => 'FS000000001',
    'customerCode'     => 123456789,
    'entityUseCode'    => 123456789,
    'currencyCode'     => 'USD',
    'exchangeRate'     => 1,
    'description'      => '描述',
    'purchaseOrderNo'  => 'FS000000001',
    'salespersonCode'  => 123,
    'lines'            =>[
        [
            'amount'=> 10000,
            'quantity'=>3,
            'description' => '描述'
        ],
        [
            'amount'=> 20000,
            'quantity'=>3,
            'description' => '描述'
        ],
    ]
])
->setLines(function ($lines) {
    foreach ($lines as $key => $value){
        if ($value['type'] ?? '' == 'shipping'){
            $lines[$key]['itemCode'] = 'Shipping';
            $lines[$key]['taxCode']  = config('avatax.shippingTaxCode');
        } else {
            $lines[$key]['itemCode'] = $value['id'] ?? '';
            $lines[$key]['taxCode']  = config('avatax.productsTaxCode');
        }

        $lines[$key]['number'] = $key + 1;
    }

    return $lines;
})
->createTransaction('SalesOrder');
```











