<?php

namespace Database\Seeders;

use App\Models\Dept;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $depts = Dept::active()->get()->keyBy('name');

        $employees = [
            ['code' => 'E001', 'name' => '山田 太郎', 'name_kana' => 'ヤマダ タロウ', 'dept' => '第一営業課', 'email' => 'yamada.taro@example.com'],
            ['code' => 'E002', 'name' => '鈴木 花子', 'name_kana' => 'スズキ ハナコ', 'dept' => '第一営業課', 'email' => 'suzuki.hanako@example.com'],
            ['code' => 'E003', 'name' => '佐藤 次郎', 'name_kana' => 'サトウ ジロウ', 'dept' => '第二営業課', 'email' => 'sato.jiro@example.com'],
            ['code' => 'E004', 'name' => '田中 美咲', 'name_kana' => 'タナカ ミサキ', 'dept' => '第三営業課', 'email' => 'tanaka.misaki@example.com'],
            ['code' => 'E005', 'name' => '伊藤 健一', 'name_kana' => 'イトウ ケンイチ', 'dept' => '人事課', 'email' => 'ito.kenichi@example.com'],
            ['code' => 'E006', 'name' => '渡辺 奈々', 'name_kana' => 'ワタナベ ナナ', 'dept' => '経理課', 'email' => 'watanabe.nana@example.com'],
            ['code' => 'E007', 'name' => '中村 剛', 'name_kana' => 'ナカムラ ツヨシ', 'dept' => 'システム開発課', 'email' => 'nakamura.tsuyoshi@example.com'],
            ['code' => 'E008', 'name' => '小林 さくら', 'name_kana' => 'コバヤシ サクラ', 'dept' => 'インフラ課', 'email' => 'kobayashi.sakura@example.com'],
            ['code' => 'E009', 'name' => '加藤 誠', 'name_kana' => 'カトウ マコト', 'dept' => 'デジタルマーケティング課', 'email' => 'kato.makoto@example.com'],
            ['code' => 'E010', 'name' => '吉田 陽子', 'name_kana' => 'ヨシダ ヨウコ', 'dept' => '広報課', 'email' => 'yoshida.yoko@example.com'],
            ['code' => 'E011', 'name' => '山本 浩二', 'name_kana' => 'ヤマモト コウジ', 'dept' => null, 'email' => 'yamamoto.koji@example.com'],
        ];

        foreach ($employees as $data) {
            Employee::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'name_kana' => $data['name_kana'],
                'dept_id' => $data['dept'] ? ($depts->get($data['dept'])?->id) : null,
                'email' => $data['email'],
            ]);
        }
    }
}
