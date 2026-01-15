#!/bin/bash

# === Disable script exit on error ===
set +e

echo "🧩 FE CRUD Generator (Vue + Pinia Store)"
echo "📁 Enter module name (e.g. admin or user):"
read MODULE_NAME

echo "📦 Enter feature name (e.g. example, category):"
read FEATURE_NAME

# Convert MODULE_NAME and FEATURE_NAME to lowercase
MODULE_NAME=$(echo "$MODULE_NAME" | tr '[:upper:]' '[:lower:]')
FEATURE_NAME=$(echo "$FEATURE_NAME" | tr '[:upper:]' '[:lower:]')

if [ -z "$MODULE_NAME" ] || [ -z "$FEATURE_NAME" ]; then
    echo "❌ Module name and feature name cannot be empty."
    exit 1
fi

echo "✅ Module name: $MODULE_NAME"
echo "✅ Feature name: $FEATURE_NAME"

# === Formatting functions ===
capitalize() {
    echo "$1" | awk '{print toupper(substr($0,1,1)) tolower(substr($0,2))}'
}

camelCase() {
    echo "$1" | awk '{print tolower(substr($0,1,1)) substr($0,2)}'
}

# === Format biến chuẩn ===
MODULE_CAMEL=$(camelCase "$MODULE_NAME")               # admin
FEATURE_CAMEL=$(camelCase "$FEATURE_NAME")             # example
MODULE_CAPITALIZED=$(capitalize "$MODULE_NAME")        # Admin
FEATURE_CAPITALIZED=$(capitalize "$FEATURE_NAME")      # Example

STORE_NAME="use${MODULE_CAPITALIZED}${FEATURE_CAPITALIZED}Store"   # useAdminExampleStore
PINIA_ID="${MODULE_CAMEL}${FEATURE_CAPITALIZED}Store"              # adminExampleStore
PLURAL="${FEATURE_CAMEL}s"                                         # examples

# === Paths ===
JS_PAGE_DIR="resources/js/pages/${MODULE_CAMEL}/${FEATURE_CAMEL}"
JS_STORE_DIR="resources/js/stores/${MODULE_CAMEL}"

INDEX_VUE_FILE="${JS_PAGE_DIR}/Index.vue"
EDIT_VUE_FILE="${JS_PAGE_DIR}/Edit.vue"
STORE_FILE="${JS_STORE_DIR}/${FEATURE_CAMEL}.js"

mkdir -p "$JS_PAGE_DIR"
mkdir -p "$JS_STORE_DIR"

# === Create Vue page file ===
if [ ! -f "$INDEX_VUE_FILE" ]; then
cat > "$INDEX_VUE_FILE" <<EOL
<template>
    <div class="flex header-content mb-5">
        <div class="header-title">
            {{ \$t('title') }}
        </div>
        <div class="header-action">
            <router-link :to="{ name: '${MODULE_CAMEL}-${FEATURE_CAMEL}-create' }">
                <VaButton color="primary" class="ml-2">
                    <template #prepend>
                        <VaIcon name="plus" />
                    </template>
                    &nbsp;{{ \$t('common.create') }}
                </VaButton>
            </router-link>
        </div>
    </div>
    <div class="search-form mb-5">
        <VaForm tag="form" ref="formRef" @submit.prevent="handleSearch">
            <div class="flex items-end gap-4">
                <div class="flex flex-1 items-center gap-2">
                    <label class="block text-sm font-medium">
                        {{ \$t('name') }}
                    </label>
                    <VaInput v-model="form_search.name" type="text" class="w-full" />
                </div>
                <div>
                    <VaButton type="submit" color="primary" preset="secondary" border-color="primary" class="btn">
                        <template #prepend><VaIcon name="search" /></template>&nbsp;
                        <span>{{ \$t('common.search') }}</span>
                    </VaButton>
                </div>
            </div>
        </VaForm>
    </div>
    <div class="va-table-responsive">
        <table class="va-table va-table--striped w-full border border-gray-300 border-collapse">
            <thead>
                <tr>
                    <th>{{ \$t('name') }}</th>
                    <th style="width: 80px"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in list_items.data" :key="item.id">
                    <td>{{ item.name }}</td>
                    <td class="text-center">
                        <router-link :to="{ name: '${MODULE_CAMEL}-${FEATURE_CAMEL}-edit', params: { ${MODULE_CAMEL}_id: item.id } }">
                            <VaButton size="small" color="primary" icon="fa-pen-to-square"></VaButton>
                        </router-link>
                    </td>
                </tr>
            </tbody>
        </table>
        <Pagination v-if="list_items && list_items.meta" :meta="list_items.meta" :items="\$t('items')" @change="handlePageChange" />
    </div>
</template>

<script setup>
    import Pagination from '@/components/partials/Pagination.vue'
    import qs from 'qs'
    import { useI18n } from 'vue-i18n'
    import { onMounted, computed, reactive, ref } from 'vue'
    import { useCommonStore } from '@/stores/common.js'
    import { ${STORE_NAME} } from '@/stores/${MODULE_CAMEL}/${FEATURE_CAMEL}'

    const commonStore = useCommonStore()
    const ${PINIA_ID} = ${STORE_NAME}()

    const list_items = computed(() => ${PINIA_ID}.get${FEATURE_CAPITALIZED})

    const { t } = useI18n()
    const condition = ref()
    const form_search = reactive({
        name: null,
    })

    onMounted(async () => {
        commonStore.startLoading()
        await ${PINIA_ID}.fetch${FEATURE_CAPITALIZED}s()
        commonStore.stopLoading()
    })

    const handlePageChange = async (pagination) => {
        commonStore.startLoading()
        if (condition.value) {
            condition.value.page = pagination.page
            condition.value.per_page = pagination.per_page
        }
        const queryString = qs.stringify(condition.value)
        await ${PINIA_ID}.fetch${FEATURE_CAPITALIZED}s(queryString)
        commonStore.stopLoading()
    }

    const handleSearch = async () => {
        commonStore.startLoading()
        const conditions = {
            filter: {
                eq: {},
                contains: {},
            },
        }
        if (form_search.name) {
            conditions.filter.contains.name = form_search.name
        }
        condition.value = conditions
        const queryString = qs.stringify(conditions)
        await ${PINIA_ID}.fetch${FEATURE_CAPITALIZED}s(queryString)
        commonStore.stopLoading()
    }
</script>
EOL

    echo "✅ Created Vue page: $VUE_FILE"
else
    echo "⚠️ Vue page already exists: $VUE_FILE"
fi

# === Create Vue Edit page file ===
# === Create Vue Edit page file ===
if [ ! -f "$EDIT_VUE_FILE" ]; then
cat > "$EDIT_VUE_FILE" <<EOL
<template>
    <div class="flex header-content mb-5">
        <div class="header-title">
            <span v-if="is_edit">{{ \$t('detail') }}</span>
            <span v-else>{{ \$t('create') }}</span>
        </div>
        <div class="header-action">
            <router-link :to="{ name: '${MODULE_CAMEL}-list' }">
                <VaButton color="primary" class="ml-2">
                    <template #prepend>
                        <VaIcon name="arrow-left" />
                    </template>
                    &nbsp;{{ \$t('list') }}
                </VaButton>
            </router-link>
        </div>
    </div>
    <div class="create-admin-form p-4">
        <VaForm tag="form" ref="formRef" @submit.prevent="submitForm">
            <div class="mb-4">
                <label class="block mb-2 text-sm font-medium">
                    {{ \$t('name') }}<span class="text-red-500">*</span>
                </label>
                <VaInput
                    v-model="form.name"
                    class="w-full"
                    type="text"
                    :rules="[requiredRule(t('name'))]"
                />
            </div>
            <div class="justify-between gap-20 btn-action-group flex-center-between">
                <VaButton type="submit" color="primary" preset="secondary" border-color="primary" class="btn">
                    <template #prepend><VaIcon name="check" /></template>&nbsp;
                    <span v-if="is_edit">{{ \$t('common.update') }}</span>
                    <span v-else>{{ \$t('common.save') }}</span>
                </VaButton>
                <VaButton v-if="is_edit" @click="handleDelete" color="danger" preset="secondary" border-color="danger" class="w-auto">
                    <template #prepend><VaIcon name="trash" /></template>&nbsp;
                    {{ \$t('common.delete') }}
                </VaButton>
            </div>
        </VaForm>
        <ModalDelete
            :is_delete="is_delete"
            @confirm-delete="handleConfirmDelete"
            @close-delete="handleCloseDelete"
        />
    </div>
</template>

<script setup lang="ts">
    import ModalDelete from '@/components/partials/ModalDelete.vue'
    import { reactive, ref, onMounted } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { useForm, useToast } from 'vuestic-ui'
    import { useRoute, useRouter } from 'vue-router'
    import { useCommonStore } from '@/stores/common.js'
    import { ${STORE_NAME} } from '@/stores/${MODULE_CAMEL}/${FEATURE_CAMEL}'

    const commonStore = useCommonStore()
    const ${PINIA_ID} = ${STORE_NAME}()

    const { validate } = useForm('formRef')
    const { init } = useToast()
    const { t } = useI18n()
    const route = useRoute()
    const router = useRouter()

    const form = reactive({
        name: null,
    })

    const is_delete = ref(false)
    const ${FEATURE_CAMEL}_id = ref(route.params.${FEATURE_CAMEL}_id || '0')
    const is_edit = ref(${FEATURE_CAMEL}_id.value !== '0')

    onMounted(async () => {
        try {
            commonStore.startLoading()
            if (${FEATURE_CAMEL}_id.value !== '0') {
                const res = await ${PINIA_ID}.fetch${FEATURE_CAPITALIZED}(${FEATURE_CAMEL}_id.value)
                if (res && res.data) {
                    Object.assign(form, res.data)
                }
            }
        } catch (error) {
            console.error(error)
        } finally {
            commonStore.stopLoading()
        }
    })

    const submitForm = async () => {
        if (validate()) {
            try {
                commonStore.startLoading();

                if (${FEATURE_CAMEL}_id.value !== '0') {
                    const response = await ${PINIA_ID}.update${FEATURE_CAPITALIZED}(form);
                    if (response) {
                        init({
                            message: t('notification.update_success'),
                            color: 'success'
                        });
                    }
                } else {
                    const response = await ${PINIA_ID}.create${FEATURE_CAPITALIZED}(form);
                    if (response) {
                        init({
                            message: t('notification.create_success'),
                            color: 'success'
                        });
                        router.push({ name: '${MODULE_CAMEL}-list' });
                    }
                }
            } catch (error) {
                const errors = error?.response?.data?.errors || {};
                const messages = Object.values(errors)
                    .flat()
                    .join('<br>');

                init({
                    message: \`<strong>\${t('notification.create_error')}</strong><br>\${messages}\`,
                    dangerouslyUseHtmlString: true,
                    color: 'danger',
                });
            } finally {
                commonStore.stopLoading();
            }
        }
    };

    const handleDelete = async () => {
        is_delete.value = true
    }

    const handleCloseDelete = () => {
        is_delete.value = false
    }

    const handleConfirmDelete = async () => {
        try {
            commonStore.startLoading()
            const response = await ${PINIA_ID}.delete${FEATURE_CAPITALIZED}(${FEATURE_CAMEL}_id.value)
            if (response) {
                init({
                    message: t('notification.delete_success'),
                    color: 'success'
                })
                router.push({ name: '${MODULE_CAMEL}-list' })
            }
        } catch (error) {
            console.error(error)
        } finally {
            is_delete.value = false
            commonStore.stopLoading()
        }
    }

    const requiredRule = (label: string) => (v) => !!v || \`\${label} \${t('validation.required')}\`
</script>
EOL

    echo "✅ Created Vue edit page: $EDIT_VUE_FILE"
else
    echo "⚠️ Vue edit page already exists: $EDIT_VUE_FILE"
fi

# === Create Store file ===
if [ ! -f "$STORE_FILE" ]; then
cat > "$STORE_FILE" <<EOL
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { adminApi } from '@/utils/${MODULE_CAMEL}/${MODULE_CAMEL}_api'

export const ${STORE_NAME} = defineStore('${PINIA_ID}', () => {
    const ${PLURAL} = ref([])

    const get${FEATURE_CAPITALIZED} = computed(() => ${PLURAL}.value);

    // Fetch all ${PLURAL}
    async function fetch${FEATURE_CAPITALIZED}s(queryString = '') {
        try {
            const response = await adminApi.get(\`${FEATURE_CAMEL}\${queryString ? \`?\${queryString}\` : ''}\`);
            ${PLURAL}.value = response.data;
        } catch (err) {
            throw err;
        }
    }

    // Fetch single ${FEATURE_CAMEL}
    async function fetch${FEATURE_CAPITALIZED}(id) {
        try {
            const response = await adminApi.get(\`${FEATURE_CAMEL}/\${id}\`);
            return response.data;
        } catch (err) {
            throw err;
        }
    }

    // Create new ${FEATURE_CAMEL}
    async function create${FEATURE_CAPITALIZED}(payload) {
        try {
            const response = await adminApi.post('${FEATURE_CAMEL}', payload);
            return response.data;
        } catch (err) {
            throw err;
        }
    }

    // Update existing ${FEATURE_CAMEL}
    async function update${FEATURE_CAPITALIZED}(payload) {
        try {
            const response = await adminApi.put(\`${FEATURE_CAMEL}/\${payload.id}\`, payload);
            return response.data;
        } catch (err) {
            throw err;
        }
    }

    // Delete ${FEATURE_CAMEL}
    async function delete${FEATURE_CAPITALIZED}(id) {
        try {
            const response = await adminApi.delete(\`${FEATURE_CAMEL}/\${id}\`);
            return response.data;
        } catch (err) {
            throw err;
        }
    }

    return {
        ${PLURAL},
        get${FEATURE_CAPITALIZED},
        fetch${FEATURE_CAPITALIZED}s,
        fetch${FEATURE_CAPITALIZED},
        create${FEATURE_CAPITALIZED},
        update${FEATURE_CAPITALIZED},
        delete${FEATURE_CAPITALIZED},
    }
})
EOL

    echo "✅ Created Store: $STORE_FILE"
else
    echo "⚠️ Store already exists: $STORE_FILE"
fi

# === Re-enable exit on error ===
set -e
echo ""
echo "✅ FE CRUD scaffold completed for '${MODULE_NAME}/${FEATURE_NAME}'!"
echo "⚠️ You must add the router manually to: resources/js/router/${MODULE_CAMEL}/index.js"
