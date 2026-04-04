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

interface Parent {
    id: number;
    name: string;
}

interface DeptFormData {
    name: string;
    parent_id: string | null;
    errors: {
        name?: string;
        parent_id?: string;
    };
    processing: boolean;
}

interface Props {
    form: DeptFormData;
    parents: Parent[];
    cancelHref: string;
    submitLabel: string;
    processingLabel: string;
}

defineProps<Props>();

const emit = defineEmits<{
    submit: [];
}>();
</script>

<template>
    <form @submit.prevent="emit('submit')" class="flex flex-col gap-5">
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
                :model-value="form.parent_id ?? '__none__'"
                @update:model-value="
                    (v) => (form.parent_id = v === '__none__' ? null : v)
                "
            >
                <SelectTrigger
                    id="parent_id"
                    :class="{ 'border-destructive': form.errors.parent_id }"
                >
                    <SelectValue placeholder="（なし）" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="__none__">（なし）</SelectItem>
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
                <Link :href="cancelHref">キャンセル</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? processingLabel : submitLabel }}
            </Button>
        </div>
    </form>
</template>
