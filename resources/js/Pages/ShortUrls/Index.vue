<template>
	<AuthenticatedLayout>
		<template #header>
			<h2 class="font-semibold text-xl text-black leading-tight">
				Short URLs
			</h2>
		</template>

		<div class="py-12">
			<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
				<!-- Add Short URL Form -->
				<div v-if="authUser?.role?.name !== 'super_admin'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
					<div class="p-6 text-gray-900">
						<h3 class="text-lg font-semibold mb-4">Create Short URL</h3>
						<form @submit.prevent="storeShortUrl" class="space-y-4">
							<div>
								<label class="block text-sm font-medium mb-2">Original URL</label>
								<input
									v-model="form.original_url"
									type="url"
									class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900"
									:class="{ 'border-red-500': errors.original_url }"
									placeholder="https://example.com"
								/>
								<span v-if="errors.original_url" class="text-red-500 text-sm">{{ errors.original_url[0] }}</span>
							</div>

							<div class="flex justify-end gap-2">
								<button
									type="button"
									@click="resetForm"
									class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md transition"
								>
									Reset
								</button>
								<button
									type="submit"
									:disabled="loading"
									class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md transition disabled:opacity-50"
								>
									{{ loading ? 'Creating...' : 'Create Short URL' }}
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>

		<!-- Short URLs Table - Full Width -->
		<div class="w-full bg-white shadow-sm">
			<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
				<h3 class="text-lg font-semibold mb-4 px-6 sm:px-0 text-gray-900">Short URLs List</h3>
				<div class="overflow-x-auto px-6 sm:px-0">
					<VueNiceTable
						:items="shortUrls"
						:fields="tableFields"
						:busy="tableLoading"
						:striped="true"
						:hover="true"
						:bordered="true"
						responsive="lg"
						:sort-by="sortBy"
						:sort-desc="sortDesc"
						@sort-changed="handleSort"
					>
						<template #table-busy>
							<div class="text-center py-4">Loading short URLs...</div>
						</template>

						<template #empty>
							<div class="text-center py-4">No short URLs found</div>
						</template>

						<template #cell_short_url="{ item }">
							<a :href="item.short_url" target="_blank" class="text-blue-600 hover:text-blue-800">{{ item.short_url }}</a>
						</template>
					</VueNiceTable>
				</div>
			</div>
		</div>
	</AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import VueNiceTable from '@/Components/VueNiceTable.vue';

const page = usePage();
const authUser = computed(() => page.props.auth.user);

const shortUrls = ref([]);
const loading = ref(false);
const tableLoading = ref(false);
const sortBy = ref('created_at');
const sortDesc = ref(true);

const errors = reactive({});

const form = reactive({
	original_url: '',
});

const tableFields = [
	{ key: 'code', label: 'Code', sortable: true },
	{ key: 'short_url', label: 'Short URL', sortable: false },
	{ key: 'original_url', label: 'Original URL', sortable: true },
	{ key: 'company_name', label: 'Company', sortable: true },
	{ key: 'created_by', label: 'Created By', sortable: true },
	{ key: 'created_at', label: 'Created At', sortable: true },
];

const resetForm = () => {
	form.original_url = '';
	Object.keys(errors).forEach(key => delete errors[key]);
};

const ensureCsrfCookie = async () => {
	// Natural Sanctum SPA flow: ensure XSRF cookie is set
	await window.axios.get('/sanctum/csrf-cookie');
};

const storeShortUrl = async () => {
	loading.value = true;
	Object.keys(errors).forEach(key => delete errors[key]);

	try {
		await ensureCsrfCookie();

		const { data } = await window.axios.post('/api/short-urls', form);
		shortUrls.value.push(data.data);
		resetForm();
		alert('Short URL created successfully!');
	} catch (error) {
		if (error.response && error.response.status === 422 && error.response.data?.errors) {
			Object.assign(errors, error.response.data.errors);
		} else {
			console.error('Error:', error);
			alert('Failed to create short URL');
		}
	} finally {
		loading.value = false;
	}
};

const handleSort = (sortByKey, sortDescValue) => {
	sortBy.value = sortByKey;
	sortDesc.value = sortDescValue;
};

const fetchShortUrls = async () => {
	tableLoading.value = true;
	try {
		await ensureCsrfCookie();

		const { data } = await window.axios.get('/api/short-urls');
		shortUrls.value = data.data;
	} catch (error) {
		console.error('Error fetching short URLs:', error);
		alert('Failed to load short URLs');
	} finally {
		tableLoading.value = false;
	}
};

onMounted(() => {
	fetchShortUrls();
});
</script>

<style scoped>
.table {
	background-color: transparent;
	color: inherit;
	width: 100%;
	min-width: 100%;
}

.table thead th {
	border-bottom: 2px solid #d1d5db;
	font-weight: 600;
	white-space: nowrap;
}

.table tbody tr:hover {
	background-color: rgba(59, 130, 246, 0.05);
	cursor: pointer;
}
</style>