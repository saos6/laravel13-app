<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'code' => 'P001', 'name' => 'ノートパソコン A15', 'name_kana' => 'ノートパソコン エーイチゴ',
                'category' => 'electronics', 'spec' => 'Core i7 / 16GB / 512GB SSD',
                'maker' => '株式会社テックジャパン', 'jan_code' => '4901234567890',
                'unit' => '台', 'price' => 128000.00, 'cost' => 95000.00, 'tax_rate' => '10',
                'has_stock' => true, 'status' => 'active', 'remarks' => null,
            ],
            [
                'code' => 'P002', 'name' => 'ワイヤレスマウス', 'name_kana' => 'ワイヤレスマウス',
                'category' => 'electronics', 'spec' => 'Bluetooth 5.0 / 3ボタン',
                'maker' => '株式会社テックジャパン', 'jan_code' => '4901234567891',
                'unit' => '個', 'price' => 3800.00, 'cost' => 2200.00, 'tax_rate' => '10',
                'has_stock' => true, 'status' => 'active', 'remarks' => null,
            ],
            [
                'code' => 'P003', 'name' => 'ボールペン（黒）10本セット', 'name_kana' => 'ボールペン クロ ジュッポンセット',
                'category' => 'stationery', 'spec' => '0.7mm / 黒インク',
                'maker' => '文具商事株式会社', 'jan_code' => '4902345678901',
                'unit' => 'セット', 'price' => 550.00, 'cost' => 300.00, 'tax_rate' => '10',
                'has_stock' => true, 'status' => 'active', 'remarks' => null,
            ],
            [
                'code' => 'P004', 'name' => 'A4コピー用紙 500枚', 'name_kana' => 'エーヨン コピーヨウシ ゴヒャクマイ',
                'category' => 'stationery', 'spec' => '坪量80g/m²',
                'maker' => '文具商事株式会社', 'jan_code' => '4902345678902',
                'unit' => '束', 'price' => 680.00, 'cost' => 420.00, 'tax_rate' => '10',
                'has_stock' => true, 'status' => 'active', 'remarks' => '箱単位での発注推奨',
            ],
            [
                'code' => 'P005', 'name' => '有機緑茶 100g', 'name_kana' => 'ユウキリョクチャ ヒャクグラム',
                'category' => 'food', 'spec' => '有機JAS認証 / 静岡産',
                'maker' => '静岡茶本舗', 'jan_code' => '4903456789012',
                'unit' => '袋', 'price' => 1200.00, 'cost' => 700.00, 'tax_rate' => '8',
                'has_stock' => true, 'status' => 'active', 'remarks' => '要冷暗所保管',
            ],
            [
                'code' => 'P006', 'name' => 'オフィスチェア エルゴ', 'name_kana' => 'オフィスチェア エルゴ',
                'category' => 'furniture', 'spec' => 'メッシュ素材 / ランバーサポート付',
                'maker' => 'ファニチャーワークス', 'jan_code' => null,
                'unit' => '脚', 'price' => 45000.00, 'cost' => 32000.00, 'tax_rate' => '10',
                'has_stock' => false, 'status' => 'active', 'remarks' => '受注生産品',
            ],
            [
                'code' => 'P007', 'name' => 'レガシーキーボード PS/2', 'name_kana' => 'レガシーキーボード ピーエスツー',
                'category' => 'electronics', 'spec' => 'PS/2接続 / 109キー',
                'maker' => '株式会社テックジャパン', 'jan_code' => '4901234560001',
                'unit' => '個', 'price' => 1500.00, 'cost' => 800.00, 'tax_rate' => '10',
                'has_stock' => true, 'status' => 'discontinued', 'remarks' => '在庫限りで廃盤',
            ],
        ];

        foreach ($products as $data) {
            Product::create($data);
        }
    }
}
