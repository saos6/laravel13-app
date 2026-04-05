<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

interface Customer {
    id: number;
    name: string;
}
interface Employee {
    id: number;
    name: string;
}
interface ProductOption {
    id: number;
    code: string;
    name: string;
    spec: string | null;
    unit: string | null;
    price: string;
    tax_rate: string;
}
interface QuoteItem {
    product_id: number | null;
    product_name: string;
    spec: string;
    quantity: string;
    unit: string;
    unit_price: string;
    tax_rate: string;
    amount: number;
    remarks: string;
}

interface FormData {
    customer_id: string;
    employee_id: string;
    quote_date: string;
    expiry_date: string;
    subject: string;
    status: string;
    remarks: string;
    items: QuoteItem[];
    [key: string]: unknown;
}

const props = defineProps<{
    form: InertiaForm<FormData>;
    customers: Customer[];
    employees: Employee[];
    products: ProductOption[];
    statuses: Record<string, string>;
    taxRates: Record<string, string>;
    cancelHref: string;
    submitLabel: string;
    processingLabel: string;
}>();

const emit = defineEmits<{ submit: [] }>();

const productMap = computed(() =>
    Object.fromEntries(props.products.map((p) => [p.id, p])),
);

function addItem() {
    props.form.items.push({
        product_id: null,
        product_name: '',
        spec: '',
        quantity: '1',
        unit: '',
        unit_price: '0',
        tax_rate: '10',
        amount: 0,
        remarks: '',
    });
}

function removeItem(index: number) {
    props.form.items.splice(index, 1);
}

function onProductChange(index: number, productId: string) {
    const id = productId === '__none__' ? null : Number(productId);
    props.form.items[index].product_id = id;

    if (id && productMap.value[id]) {
        const p = productMap.value[id];
        props.form.items[index].product_name = p.name;
        props.form.items[index].spec = p.spec ?? '';
        props.form.items[index].unit = p.unit ?? '';
        props.form.items[index].unit_price = p.price ?? '0';
        props.form.items[index].tax_rate = p.tax_rate ?? '10';
    }

    recalcItem(index);
}

function recalcItem(index: number) {
    const item = props.form.items[index];
    const qty = parseFloat(item.quantity) || 0;
    const price = parseFloat(item.unit_price) || 0;
    item.amount = Math.round(qty * price * 100) / 100;
}

const subtotal = computed(() =>
    props.form.items.reduce((s, i) => s + (i.amount || 0), 0),
);

const taxAmount = computed(() =>
    props.form.items.reduce((s, i) => {
        const rate = parseInt(i.tax_rate) || 0;

        return s + Math.round((i.amount || 0) * rate) / 100;
    }, 0),
);

const totalAmount = computed(() => subtotal.value + taxAmount.value);

function fmt(val: number): string {
    return '¥' + val.toLocaleString('ja-JP', { minimumFractionDigits: 0 });
}

function getItemError(index: number, field: string): string | undefined {
    return (props.form.errors as Record<string, string>)[
        `items.${index}.${field}`
    ];
}
</script>

<template>
    <form @submit.prevent="emit('submit')" class="flex flex-col gap-6">
        <!-- ヘッダー情報 -->
        <div class="rounded-md border p-4">
            <h2 class="mb-4 text-sm font-semibold text-muted-foreground">
                基本情報
            </h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- 得意先 -->
                <div class="grid gap-1.5">
                    <Label
                        >得意先 <span class="text-destructive">*</span></Label
                    >
                    <Select
                        :model-value="form.customer_id"
                        @update:model-value="(v) => (form.customer_id = v)"
                    >
                        <SelectTrigger
                            :class="{
                                'border-destructive': form.errors.customer_id,
                            }"
                        >
                            <SelectValue placeholder="選択してください" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="c in customers"
                                :key="c.id"
                                :value="String(c.id)"
                                >{{ c.name }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
                    <p
                        v-if="form.errors.customer_id"
                        class="text-xs text-destructive"
                    >
                        {{ form.errors.customer_id }}
                    </p>
                </div>

                <!-- 担当者 -->
                <div class="grid gap-1.5">
                    <Label>担当者</Label>
                    <Select
                        :model-value="form.employee_id || '__none__'"
                        @update:model-value="
                            (v) =>
                                (form.employee_id = v === '__none__' ? '' : v)
                        "
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="未設定" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__none__">未設定</SelectItem>
                            <SelectItem
                                v-for="e in employees"
                                :key="e.id"
                                :value="String(e.id)"
                                >{{ e.name }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
                </div>

                <!-- 見積日 -->
                <div class="grid gap-1.5">
                    <Label
                        >見積日 <span class="text-destructive">*</span></Label
                    >
                    <Input
                        type="date"
                        v-model="form.quote_date"
                        :class="{
                            'border-destructive': form.errors.quote_date,
                        }"
                    />
                    <p
                        v-if="form.errors.quote_date"
                        class="text-xs text-destructive"
                    >
                        {{ form.errors.quote_date }}
                    </p>
                </div>

                <!-- 有効期限 -->
                <div class="grid gap-1.5">
                    <Label>有効期限</Label>
                    <Input
                        type="date"
                        v-model="form.expiry_date"
                        :class="{
                            'border-destructive': form.errors.expiry_date,
                        }"
                    />
                    <p
                        v-if="form.errors.expiry_date"
                        class="text-xs text-destructive"
                    >
                        {{ form.errors.expiry_date }}
                    </p>
                </div>

                <!-- 件名 -->
                <div class="grid gap-1.5 sm:col-span-2">
                    <Label>件名 <span class="text-destructive">*</span></Label>
                    <Input
                        v-model="form.subject"
                        placeholder="例: ○○システム導入費用"
                        :class="{ 'border-destructive': form.errors.subject }"
                    />
                    <p
                        v-if="form.errors.subject"
                        class="text-xs text-destructive"
                    >
                        {{ form.errors.subject }}
                    </p>
                </div>

                <!-- ステータス -->
                <div class="grid gap-1.5">
                    <Label
                        >ステータス
                        <span class="text-destructive">*</span></Label
                    >
                    <Select
                        :model-value="form.status"
                        @update:model-value="(v) => (form.status = v)"
                    >
                        <SelectTrigger
                            :class="{
                                'border-destructive': form.errors.status,
                            }"
                        >
                            <SelectValue placeholder="選択してください" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="(label, key) in statuses"
                                :key="key"
                                :value="key"
                                >{{ label }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
                    <p
                        v-if="form.errors.status"
                        class="text-xs text-destructive"
                    >
                        {{ form.errors.status }}
                    </p>
                </div>

                <!-- 備考 -->
                <div class="grid gap-1.5 sm:col-span-2">
                    <Label>備考</Label>
                    <Textarea
                        v-model="form.remarks"
                        rows="3"
                        placeholder="備考・特記事項"
                    />
                </div>
            </div>
        </div>

        <!-- 明細 -->
        <div class="rounded-md border p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-muted-foreground">
                    明細
                </h2>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addItem"
                >
                    <Plus class="mr-1 h-4 w-4" />行追加
                </Button>
            </div>

            <p v-if="form.errors.items" class="mb-2 text-xs text-destructive">
                {{ form.errors.items }}
            </p>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium">
                                商品選択
                            </th>
                            <th class="px-3 py-2 text-left font-medium">
                                品名 <span class="text-destructive">*</span>
                            </th>
                            <th class="px-3 py-2 text-left font-medium">
                                仕様
                            </th>
                            <th class="px-3 py-2 text-right font-medium">
                                数量 <span class="text-destructive">*</span>
                            </th>
                            <th class="px-3 py-2 text-left font-medium">
                                単位
                            </th>
                            <th class="px-3 py-2 text-right font-medium">
                                単価 <span class="text-destructive">*</span>
                            </th>
                            <th class="px-3 py-2 text-center font-medium">
                                税率
                            </th>
                            <th class="px-3 py-2 text-right font-medium">
                                金額
                            </th>
                            <th class="px-3 py-2 text-left font-medium">
                                備考
                            </th>
                            <th class="w-10 px-2 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(item, i) in form.items"
                            :key="i"
                            class="border-t"
                        >
                            <!-- 商品選択 -->
                            <td class="px-2 py-1.5">
                                <Select
                                    :model-value="
                                        item.product_id
                                            ? String(item.product_id)
                                            : '__none__'
                                    "
                                    @update:model-value="
                                        (v) => onProductChange(i, v)
                                    "
                                >
                                    <SelectTrigger class="h-8 w-36">
                                        <SelectValue placeholder="商品選択" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="__none__"
                                            >—</SelectItem
                                        >
                                        <SelectItem
                                            v-for="p in products"
                                            :key="p.id"
                                            :value="String(p.id)"
                                        >
                                            {{ p.code }} {{ p.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </td>
                            <!-- 品名 -->
                            <td class="px-2 py-1.5">
                                <Input
                                    v-model="item.product_name"
                                    class="h-8 min-w-36"
                                    :class="{
                                        'border-destructive': getItemError(
                                            i,
                                            'product_name',
                                        ),
                                    }"
                                />
                            </td>
                            <!-- 仕様 -->
                            <td class="px-2 py-1.5">
                                <Input v-model="item.spec" class="h-8 w-28" />
                            </td>
                            <!-- 数量 -->
                            <td class="px-2 py-1.5">
                                <Input
                                    v-model="item.quantity"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    class="h-8 w-20 text-right tabular-nums"
                                    :class="{
                                        'border-destructive': getItemError(
                                            i,
                                            'quantity',
                                        ),
                                    }"
                                    @input="recalcItem(i)"
                                />
                            </td>
                            <!-- 単位 -->
                            <td class="px-2 py-1.5">
                                <Input v-model="item.unit" class="h-8 w-16" />
                            </td>
                            <!-- 単価 -->
                            <td class="px-2 py-1.5">
                                <Input
                                    v-model="item.unit_price"
                                    type="number"
                                    step="1"
                                    min="0"
                                    class="h-8 w-24 text-right tabular-nums"
                                    :class="{
                                        'border-destructive': getItemError(
                                            i,
                                            'unit_price',
                                        ),
                                    }"
                                    @input="recalcItem(i)"
                                />
                            </td>
                            <!-- 税率 -->
                            <td class="px-2 py-1.5">
                                <Select
                                    :model-value="item.tax_rate"
                                    @update:model-value="
                                        (v) => {
                                            item.tax_rate = v;
                                            recalcItem(i);
                                        }
                                    "
                                >
                                    <SelectTrigger class="h-8 w-20">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="(label, key) in taxRates"
                                            :key="key"
                                            :value="key"
                                            >{{ label }}</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </td>
                            <!-- 金額 -->
                            <td class="px-2 py-1.5 text-right tabular-nums">
                                {{ fmt(item.amount) }}
                            </td>
                            <!-- 備考 -->
                            <td class="px-2 py-1.5">
                                <Input
                                    v-model="item.remarks"
                                    class="h-8 w-28"
                                />
                            </td>
                            <!-- 削除 -->
                            <td class="px-2 py-1.5 text-center">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    class="h-8 w-8 text-destructive hover:text-destructive"
                                    :disabled="form.items.length <= 1"
                                    @click="removeItem(i)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                    <!-- 合計フッター -->
                    <tfoot class="border-t bg-muted/30 font-medium">
                        <tr>
                            <td
                                colspan="7"
                                class="px-3 py-2 text-right text-muted-foreground"
                            >
                                小計
                            </td>
                            <td class="px-3 py-2 text-right tabular-nums">
                                {{ fmt(subtotal) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td
                                colspan="7"
                                class="px-3 py-2 text-right text-muted-foreground"
                            >
                                消費税
                            </td>
                            <td class="px-3 py-2 text-right tabular-nums">
                                {{ fmt(taxAmount) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        <tr class="text-base">
                            <td colspan="7" class="px-3 py-2 text-right">
                                合計
                            </td>
                            <td
                                class="px-3 py-2 text-right font-bold tabular-nums"
                            >
                                {{ fmt(totalAmount) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ボタン -->
        <div class="flex justify-end gap-2">
            <Button type="button" variant="outline" as-child>
                <a :href="cancelHref">キャンセル</a>
            </Button>
            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? processingLabel : submitLabel }}
            </Button>
        </div>
    </form>
</template>
