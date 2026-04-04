<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
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

interface Employee {
    id: number;
    code: string;
    name: string;
}

interface CustomerFormData {
    code: string;
    name: string;
    name_kana: string;
    postal_code: string;
    address: string;
    phone: string;
    fax: string;
    email: string;
    employee_id: string | null;
    closing_day: string | null;
    payment_cycle: string | null;
    payment_day: string | null;
    remarks: string;
    errors: {
        code?: string;
        name?: string;
        name_kana?: string;
        postal_code?: string;
        address?: string;
        phone?: string;
        fax?: string;
        email?: string;
        employee_id?: string;
        closing_day?: string;
        payment_cycle?: string;
        payment_day?: string;
        remarks?: string;
    };
    processing: boolean;
}

interface Props {
    form: CustomerFormData;
    employees: Employee[];
    paymentCycles: Record<string, string>;
    cancelHref: string;
    submitLabel: string;
    processingLabel: string;
}

defineProps<Props>();
const emit = defineEmits<{ submit: [] }>();

const dayOptions = Array.from({ length: 30 }, (_, i) => ({
    value: String(i + 1),
    label: `${i + 1}日`,
}));
dayOptions.push({ value: '31', label: '末日' });
</script>

<template>
    <form @submit.prevent="emit('submit')" class="flex flex-col gap-5">
        <!-- 基本情報 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <!-- 得意先コード -->
            <div class="flex flex-col gap-1.5">
                <Label for="code">
                    得意先コード
                    <span class="ml-1 text-xs text-destructive">*必須</span>
                </Label>
                <Input
                    id="code"
                    v-model="form.code"
                    placeholder="例：C001"
                    maxlength="20"
                    :class="{ 'border-destructive': form.errors.code }"
                />
                <InputError :message="form.errors.code" />
            </div>

            <!-- 担当社員 -->
            <div class="flex flex-col gap-1.5">
                <Label for="employee_id">担当社員</Label>
                <Select
                    :model-value="form.employee_id ?? '__none__'"
                    @update:model-value="
                        (v) => (form.employee_id = v === '__none__' ? null : v)
                    "
                >
                    <SelectTrigger
                        id="employee_id"
                        :class="{
                            'border-destructive': form.errors.employee_id,
                        }"
                    >
                        <SelectValue placeholder="（なし）" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__none__">（なし）</SelectItem>
                        <SelectItem
                            v-for="emp in employees"
                            :key="emp.id"
                            :value="String(emp.id)"
                        >
                            [{{ emp.code }}] {{ emp.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.employee_id" />
            </div>
        </div>

        <!-- 得意先名 -->
        <div class="flex flex-col gap-1.5">
            <Label for="name">
                得意先名
                <span class="ml-1 text-xs text-destructive">*必須</span>
            </Label>
            <Input
                id="name"
                v-model="form.name"
                placeholder="例：株式会社山田商事"
                maxlength="100"
                :class="{ 'border-destructive': form.errors.name }"
            />
            <InputError :message="form.errors.name" />
        </div>

        <!-- 得意先名カナ -->
        <div class="flex flex-col gap-1.5">
            <Label for="name_kana">得意先名カナ</Label>
            <Input
                id="name_kana"
                v-model="form.name_kana"
                placeholder="例：カブシキガイシャヤマダショウジ"
                maxlength="200"
                :class="{ 'border-destructive': form.errors.name_kana }"
            />
            <InputError :message="form.errors.name_kana" />
        </div>

        <!-- 住所 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="flex flex-col gap-1.5">
                <Label for="postal_code">郵便番号</Label>
                <Input
                    id="postal_code"
                    v-model="form.postal_code"
                    placeholder="例：100-0001"
                    maxlength="8"
                    :class="{ 'border-destructive': form.errors.postal_code }"
                />
                <InputError :message="form.errors.postal_code" />
            </div>
            <div class="flex flex-col gap-1.5 sm:col-span-2">
                <Label for="address">住所</Label>
                <Input
                    id="address"
                    v-model="form.address"
                    placeholder="例：東京都千代田区千代田1-1"
                    maxlength="255"
                    :class="{ 'border-destructive': form.errors.address }"
                />
                <InputError :message="form.errors.address" />
            </div>
        </div>

        <!-- 連絡先 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="flex flex-col gap-1.5">
                <Label for="phone">電話番号</Label>
                <Input
                    id="phone"
                    v-model="form.phone"
                    placeholder="例：03-1234-5678"
                    maxlength="20"
                    :class="{ 'border-destructive': form.errors.phone }"
                />
                <InputError :message="form.errors.phone" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="fax">FAX番号</Label>
                <Input
                    id="fax"
                    v-model="form.fax"
                    placeholder="例：03-1234-5679"
                    maxlength="20"
                    :class="{ 'border-destructive': form.errors.fax }"
                />
                <InputError :message="form.errors.fax" />
            </div>
            <div class="flex flex-col gap-1.5">
                <Label for="email">メールアドレス</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    placeholder="例：info@example.com"
                    maxlength="255"
                    :class="{ 'border-destructive': form.errors.email }"
                />
                <InputError :message="form.errors.email" />
            </div>
        </div>

        <!-- 支払い情報 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <!-- 締め日 -->
            <div class="flex flex-col gap-1.5">
                <Label for="closing_day">締め日</Label>
                <Select
                    :model-value="form.closing_day ?? '__none__'"
                    @update:model-value="
                        (v) => (form.closing_day = v === '__none__' ? null : v)
                    "
                >
                    <SelectTrigger
                        id="closing_day"
                        :class="{
                            'border-destructive': form.errors.closing_day,
                        }"
                    >
                        <SelectValue placeholder="（なし）" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__none__">（なし）</SelectItem>
                        <SelectItem
                            v-for="opt in dayOptions"
                            :key="opt.value"
                            :value="opt.value"
                        >
                            {{ opt.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.closing_day" />
            </div>

            <!-- 支払いサイクル -->
            <div class="flex flex-col gap-1.5">
                <Label for="payment_cycle">支払いサイクル</Label>
                <Select
                    :model-value="form.payment_cycle ?? '__none__'"
                    @update:model-value="
                        (v) =>
                            (form.payment_cycle = v === '__none__' ? null : v)
                    "
                >
                    <SelectTrigger
                        id="payment_cycle"
                        :class="{
                            'border-destructive': form.errors.payment_cycle,
                        }"
                    >
                        <SelectValue placeholder="（なし）" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__none__">（なし）</SelectItem>
                        <SelectItem
                            v-for="(label, value) in paymentCycles"
                            :key="value"
                            :value="value"
                        >
                            {{ label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.payment_cycle" />
            </div>

            <!-- 支払日 -->
            <div class="flex flex-col gap-1.5">
                <Label for="payment_day">支払日</Label>
                <Select
                    :model-value="form.payment_day ?? '__none__'"
                    @update:model-value="
                        (v) => (form.payment_day = v === '__none__' ? null : v)
                    "
                >
                    <SelectTrigger
                        id="payment_day"
                        :class="{
                            'border-destructive': form.errors.payment_day,
                        }"
                    >
                        <SelectValue placeholder="（なし）" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__none__">（なし）</SelectItem>
                        <SelectItem
                            v-for="opt in dayOptions"
                            :key="opt.value"
                            :value="opt.value"
                        >
                            {{ opt.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.payment_day" />
            </div>
        </div>

        <!-- 備考 -->
        <div class="flex flex-col gap-1.5">
            <Label for="remarks">備考</Label>
            <Textarea
                id="remarks"
                v-model="form.remarks"
                placeholder="備考を入力してください"
                rows="3"
                maxlength="1000"
                :class="{ 'border-destructive': form.errors.remarks }"
            />
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
