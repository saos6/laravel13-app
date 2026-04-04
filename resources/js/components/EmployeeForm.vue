<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue';

interface Dept {
    id: number;
    name: string;
}

interface EmployeeFormData {
    code: string;
    name: string;
    name_kana: string;
    dept_id: string | null;
    email: string;
    errors: {
        code?: string;
        name?: string;
        name_kana?: string;
        dept_id?: string;
        email?: string;
    };
    processing: boolean;
}

interface Props {
    form: EmployeeFormData;
    depts: Dept[];
    cancelHref: string;
    submitLabel: string;
    processingLabel: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    submit: [];
}>();
</script>

<template>
    <form @submit.prevent="emit('submit')" class="flex flex-col gap-5">
        <!-- 社員コード -->
        <div class="flex flex-col gap-1.5">
            <Label for="code">
                社員コード
                <span class="ml-1 text-xs text-destructive">*必須</span>
            </Label>
            <Input
                id="code"
                v-model="form.code"
                placeholder="例：E001"
                maxlength="20"
                :class="{ 'border-destructive': form.errors.code }"
            />
            <InputError :message="form.errors.code" />
        </div>

        <!-- 氏名 -->
        <div class="flex flex-col gap-1.5">
            <Label for="name">
                氏名
                <span class="ml-1 text-xs text-destructive">*必須</span>
            </Label>
            <Input
                id="name"
                v-model="form.name"
                placeholder="例：山田 太郎"
                maxlength="50"
                :class="{ 'border-destructive': form.errors.name }"
            />
            <InputError :message="form.errors.name" />
        </div>

        <!-- 氏名カナ -->
        <div class="flex flex-col gap-1.5">
            <Label for="name_kana">氏名カナ</Label>
            <Input
                id="name_kana"
                v-model="form.name_kana"
                placeholder="例：ヤマダ タロウ"
                maxlength="100"
                :class="{ 'border-destructive': form.errors.name_kana }"
            />
            <InputError :message="form.errors.name_kana" />
        </div>

        <!-- 所属 -->
        <div class="flex flex-col gap-1.5">
            <Label for="dept_id">所属</Label>
            <Select
                :model-value="form.dept_id ?? '__none__'"
                @update:model-value="(v) => (form.dept_id = v === '__none__' ? null : v)"
            >
                <SelectTrigger id="dept_id" :class="{ 'border-destructive': form.errors.dept_id }">
                    <SelectValue placeholder="（なし）" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="__none__">（なし）</SelectItem>
                    <SelectItem v-for="dept in depts" :key="dept.id" :value="String(dept.id)">
                        {{ dept.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="form.errors.dept_id" />
        </div>

        <!-- メールアドレス -->
        <div class="flex flex-col gap-1.5">
            <Label for="email">メールアドレス</Label>
            <Input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="例：yamada@example.com"
                maxlength="255"
                :class="{ 'border-destructive': form.errors.email }"
            />
            <InputError :message="form.errors.email" />
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
