<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';

interface ProductFormData {
    code: string;
    name: string;
    name_kana: string;
    category: string | null;
    spec: string;
    maker: string;
    jan_code: string;
    unit: string;
    price: string;
    cost: string;
    tax_rate: string | null;
    has_stock: boolean;
    status: string;
    remarks: string;
    errors: {
        code?: string; name?: string; name_kana?: string; category?: string;
        spec?: string; maker?: string; jan_code?: string; unit?: string;
        price?: string; cost?: string; tax_rate?: string; has_stock?: string;
        status?: string; remarks?: string;
    };
    processing: boolean;
}

interface Props {
    form: ProductFormData;
    categories: Record<string, string>;
    taxRates: Record<string, string>;
    statuses: Record<string, string>;
    cancelHref: string;
    submitLabel: string;
    processingLabel: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{ submit: [] }>();
</script>

<template>
    <form @submit.prevent="emit('submit')" class="flex flex-col gap-5">

        <!-- コード・状態 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="flex flex-col gap-1.5">
                <Label for="code">
                    商品コード
                    <span class="ml-1 text-xs text-destructive">*必須</span>
                </Label>
                <Input id="code" v-model="form.code" placeholder="例：P001" maxlength="20"
                    :class="{ 'border-destructive': form.errors.code }" />
                <InputError :message="form.errors.code" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="category">カテゴリ</Label>
                <Select :model-value="form.category ?? '__none__'"
                    @update:model-value="(v) => (form.category = v === '__none__' ? null : v)">
                    <SelectTrigger id="category" :class="{ 'border-destructive': form.errors.category }">
                        <SelectValue placeholder="（未選択）" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__none__">（未選択）</SelectItem>
                        <SelectItem v-for="(label, value) in categories" :key="value" :value="value">
                            {{ label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.category" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="status">
                    状態
                    <span class="ml-1 text-xs text-destructive">*必須</span>
                </Label>
                <Select :model-value="form.status" @update:model-value="(v) => (form.status = v)">
                    <SelectTrigger id="status" :class="{ 'border-destructive': form.errors.status }">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="(label, value) in statuses" :key="value" :value="value">
                            {{ label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.status" />
            </div>
        </div>

        <!-- 商品名 -->
        <div class="flex flex-col gap-1.5">
            <Label for="name">
                商品名
                <span class="ml-1 text-xs text-destructive">*必須</span>
            </Label>
            <Input id="name" v-model="form.name" placeholder="例：ノートパソコン A15" maxlength="100"
                :class="{ 'border-destructive': form.errors.name }" />
            <InputError :message="form.errors.name" />
        </div>

        <!-- 商品名カナ -->
        <div class="flex flex-col gap-1.5">
            <Label for="name_kana">商品名カナ</Label>
            <Input id="name_kana" v-model="form.name_kana" placeholder="例：ノートパソコン エーイチゴ" maxlength="200"
                :class="{ 'border-destructive': form.errors.name_kana }" />
            <InputError :message="form.errors.name_kana" />
        </div>

        <!-- 仕様・メーカー -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="flex flex-col gap-1.5">
                <Label for="spec">仕様・型番・バリエーション</Label>
                <Input id="spec" v-model="form.spec" placeholder="例：Core i7 / 16GB / 512GB SSD" maxlength="255"
                    :class="{ 'border-destructive': form.errors.spec }" />
                <InputError :message="form.errors.spec" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="maker">メーカー</Label>
                <Input id="maker" v-model="form.maker" placeholder="例：株式会社テックジャパン" maxlength="100"
                    :class="{ 'border-destructive': form.errors.maker }" />
                <InputError :message="form.errors.maker" />
            </div>
        </div>

        <!-- JANコード・単位 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="flex flex-col gap-1.5 sm:col-span-2">
                <Label for="jan_code">JANコード</Label>
                <Input id="jan_code" v-model="form.jan_code" placeholder="例：4901234567890（13桁）" maxlength="13"
                    :class="{ 'border-destructive': form.errors.jan_code }" />
                <InputError :message="form.errors.jan_code" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="unit">単位</Label>
                <Input id="unit" v-model="form.unit" placeholder="例：個、本、kg" maxlength="20"
                    :class="{ 'border-destructive': form.errors.unit }" />
                <InputError :message="form.errors.unit" />
            </div>
        </div>

        <!-- 価格・税率 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="flex flex-col gap-1.5">
                <Label for="price">販売単価（円）</Label>
                <Input id="price" v-model="form.price" type="number" min="0" step="1" placeholder="例：128000"
                    :class="{ 'border-destructive': form.errors.price }" />
                <InputError :message="form.errors.price" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="cost">仕入単価（円）</Label>
                <Input id="cost" v-model="form.cost" type="number" min="0" step="1" placeholder="例：95000"
                    :class="{ 'border-destructive': form.errors.cost }" />
                <InputError :message="form.errors.cost" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="tax_rate">税率</Label>
                <Select :model-value="form.tax_rate ?? '__none__'"
                    @update:model-value="(v) => (form.tax_rate = v === '__none__' ? null : v)">
                    <SelectTrigger id="tax_rate" :class="{ 'border-destructive': form.errors.tax_rate }">
                        <SelectValue placeholder="（未選択）" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__none__">（未選択）</SelectItem>
                        <SelectItem v-for="(label, value) in taxRates" :key="value" :value="value">
                            {{ label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.tax_rate" />
            </div>
        </div>

        <!-- 在庫管理 -->
        <div class="flex items-center gap-2">
            <Checkbox
                id="has_stock"
                :checked="form.has_stock"
                @update:checked="(v) => (form.has_stock = v)"
            />
            <Label for="has_stock" class="cursor-pointer">在庫管理する</Label>
            <InputError :message="form.errors.has_stock" />
        </div>

        <!-- 備考 -->
        <div class="flex flex-col gap-1.5">
            <Label for="remarks">備考</Label>
            <Textarea id="remarks" v-model="form.remarks" placeholder="備考を入力してください" rows="3" maxlength="1000"
                :class="{ 'border-destructive': form.errors.remarks }" />
            <InputError :message="form.errors.remarks" />
        </div>

        <!-- ボタン -->
        <div class="flex justify-end gap-2 pt-2">
            <Button variant="outline" type="button" as-child>
                <Link :href="cancelHref">キャンセル</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? processingLabel : submitLabel }}
            </Button>
        </div>
    </form>
</template>
