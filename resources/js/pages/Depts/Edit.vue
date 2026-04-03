<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import DeptController from '@/actions/App/Http/Controllers/DeptController';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import type { BreadcrumbItem } from '@/types';

interface Parent {
    id: number;
    name: string;
}

interface Dept {
    id: number;
    name: string;
    parent_id: number | null;
}

const props = defineProps<{
    dept: Dept;
    parents: Parent[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '所属マスタ', href: DeptController.index.url() },
    { title: '編集', href: DeptController.edit.url(props.dept.id) },
];

const form = useForm({
    name: props.dept.name,
    parent_id: props.dept.parent_id ? String(props.dept.parent_id) : null as string | null,
});

function submit() {
    form.put(DeptController.update.url(props.dept.id));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`所属マスタ 編集 - ${dept.name}`" />

        <div class="mx-auto max-w-lg p-4">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <h1 class="mb-6 text-xl font-bold">所属 編集</h1>

                <form @submit.prevent="submit" class="flex flex-col gap-5">
                    <!-- 所属名 -->
                    <div class="flex flex-col gap-1.5">
                        <Label for="name">
                            所属名
                            <span class="ml-1 text-xs text-destructive">*必須</span>
                        </Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="例：営業部"
                            maxlength="100"
                            :class="{ 'border-destructive': form.errors.name }"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <!-- 親所属 -->
                    <div class="flex flex-col gap-1.5">
                        <Label for="parent_id">親所属</Label>
                        <Select
                            :model-value="form.parent_id ?? ''"
                            @update:model-value="(v) => (form.parent_id = v || null)"
                        >
                            <SelectTrigger id="parent_id" :class="{ 'border-destructive': form.errors.parent_id }">
                                <SelectValue placeholder="（なし）" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">（なし）</SelectItem>
                                <SelectItem
                                    v-for="parent in parents"
                                    :key="parent.id"
                                    :value="String(parent.id)"
                                >
                                    {{ parent.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.parent_id" />
                    </div>

                    <!-- ボタン -->
                    <div class="flex justify-end gap-2 pt-2">
                        <Button variant="outline" type="button" as-child>
                            <Link :href="DeptController.index.url()">キャンセル</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? '更新中...' : '更新' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
