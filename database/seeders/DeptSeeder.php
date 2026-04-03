<?php

namespace Database\Seeders;

use App\Models\Dept;
use Illuminate\Database\Seeder;

class DeptSeeder extends Seeder
{
    public function run(): void
    {
        // 本社
        $honsha = Dept::create(['name' => '本社', 'parent_id' => null]);

        // 営業部
        $eigyo = Dept::create(['name' => '営業部', 'parent_id' => $honsha->id]);
        Dept::create(['name' => '第一営業課', 'parent_id' => $eigyo->id]);
        Dept::create(['name' => '第二営業課', 'parent_id' => $eigyo->id]);
        Dept::create(['name' => '第三営業課', 'parent_id' => $eigyo->id]);

        // 総務部
        $somu = Dept::create(['name' => '総務部', 'parent_id' => $honsha->id]);
        Dept::create(['name' => '人事課', 'parent_id' => $somu->id]);
        Dept::create(['name' => '経理課', 'parent_id' => $somu->id]);
        Dept::create(['name' => '法務課', 'parent_id' => $somu->id]);

        // 開発部
        $kaihatsu = Dept::create(['name' => '開発部', 'parent_id' => $honsha->id]);
        Dept::create(['name' => 'システム開発課', 'parent_id' => $kaihatsu->id]);
        Dept::create(['name' => 'インフラ課', 'parent_id' => $kaihatsu->id]);
        Dept::create(['name' => '品質管理課', 'parent_id' => $kaihatsu->id]);

        // マーケティング部
        $marketing = Dept::create(['name' => 'マーケティング部', 'parent_id' => $honsha->id]);
        Dept::create(['name' => 'デジタルマーケティング課', 'parent_id' => $marketing->id]);
        Dept::create(['name' => '広報課', 'parent_id' => $marketing->id]);

        // 大阪支社
        $osaka = Dept::create(['name' => '大阪支社', 'parent_id' => null]);
        $osaka_eigyo = Dept::create(['name' => '営業部', 'parent_id' => $osaka->id]);
        Dept::create(['name' => '第一営業課', 'parent_id' => $osaka_eigyo->id]);
        Dept::create(['name' => '第二営業課', 'parent_id' => $osaka_eigyo->id]);
    }
}
