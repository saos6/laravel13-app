<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::active()->get()->keyBy('code');

        $customers = [
            [
                'code' => 'C001', 'name' => '株式会社山田商事', 'name_kana' => 'カブシキガイシャヤマダショウジ',
                'postal_code' => '100-0001', 'address' => '東京都千代田区千代田1-1',
                'phone' => '03-1234-5678', 'fax' => '03-1234-5679',
                'email' => 'info@yamada-shoji.example.com',
                'employee_code' => 'E001',
                'closing_day' => 31, 'payment_cycle' => 'monthly', 'payment_day' => 31,
                'remarks' => '大口顧客',
            ],
            [
                'code' => 'C002', 'name' => '鈴木産業株式会社', 'name_kana' => 'スズキサンギョウカブシキガイシャ',
                'postal_code' => '530-0001', 'address' => '大阪府大阪市北区梅田1-1',
                'phone' => '06-2345-6789', 'fax' => null,
                'email' => 'contact@suzuki-sangyo.example.com',
                'employee_code' => 'E003',
                'closing_day' => 20, 'payment_cycle' => 'monthly', 'payment_day' => 10,
                'remarks' => null,
            ],
            [
                'code' => 'C003', 'name' => '佐藤電機工業', 'name_kana' => 'サトウデンキコウギョウ',
                'postal_code' => '460-0001', 'address' => '愛知県名古屋市中区栄1-1',
                'phone' => '052-3456-7890', 'fax' => '052-3456-7891',
                'email' => null,
                'employee_code' => 'E001',
                'closing_day' => 31, 'payment_cycle' => 'bimonthly', 'payment_day' => 20,
                'remarks' => '支払いサイクル要注意',
            ],
            [
                'code' => 'C004', 'name' => '田中フーズ株式会社', 'name_kana' => 'タナカフーズカブシキガイシャ',
                'postal_code' => '812-0001', 'address' => '福岡県福岡市博多区博多駅前1-1',
                'phone' => '092-4567-8901', 'fax' => null,
                'email' => 'sales@tanaka-foods.example.com',
                'employee_code' => 'E004',
                'closing_day' => 15, 'payment_cycle' => 'quarterly', 'payment_day' => 31,
                'remarks' => null,
            ],
            [
                'code' => 'C005', 'name' => '伊藤物産', 'name_kana' => 'イトウブッサン',
                'postal_code' => null, 'address' => null,
                'phone' => '011-5678-9012', 'fax' => null,
                'email' => 'ito-bussan@example.com',
                'employee_code' => 'E003',
                'closing_day' => null, 'payment_cycle' => 'annually', 'payment_day' => null,
                'remarks' => '年払い契約',
            ],
        ];

        foreach ($customers as $data) {
            Customer::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'name_kana' => $data['name_kana'],
                'postal_code' => $data['postal_code'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'fax' => $data['fax'],
                'email' => $data['email'],
                'employee_id' => $employees->get($data['employee_code'])?->id,
                'closing_day' => $data['closing_day'],
                'payment_cycle' => $data['payment_cycle'],
                'payment_day' => $data['payment_day'],
                'remarks' => $data['remarks'],
            ]);
        }
    }
}
