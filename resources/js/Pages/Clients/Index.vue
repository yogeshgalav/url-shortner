<template>
	<AuthenticatedLayout>
		<template #header>
			<h2 class="font-semibold text-xl text-black leading-tight">
				Clients
			</h2>
		</template>

		<div class="py-12">
			<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
				<!-- Add Client Form -->
				<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
					<div class="p-6 text-gray-900">
						<h3 class="text-lg font-semibold mb-4">Add New Admin/Members</h3>
						<form @submit.prevent="storeClient" class="space-y-4">
							<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
								<div>
									<label class="block text-sm font-medium mb-2">Name</label>
									<input
										v-model="form.name"
										type="text"
										class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900"
										:class="{ 'border-red-500': errors.name }"
										placeholder="Client name"
									/>
									<span v-if="errors.name" class="text-red-500 text-sm">{{ errors.name[0] }}</span>
								</div>

								<div>
									<label class="block text-sm font-medium mb-2">Email</label>
									<input
										v-model="form.email"
										type="email"
										class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900"
										:class="{ 'border-red-500': errors.email }"
										placeholder="Email address"
									/>
									<span v-if="errors.email" class="text-red-500 text-sm">{{ errors.email[0] }}</span>
								</div>

								<div>
									<label class="block text-sm font-medium mb-2">Password</label>
									<input
										v-model="form.password"
										type="password"
										class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900"
										:class="{ 'border-red-500': errors.password }"
										placeholder="Password"
									/>
									<span v-if="errors.password" class="text-red-500 text-sm">{{ errors.password[0] }}</span>
								</div>

								<div>
									<label class="block text-sm font-medium mb-2">Role</label>
									<select
										v-if="authUser?.role?.name !== 'member'"
										v-model="form.role"
										class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900"
										:class="{ 'border-red-500': errors.role }"
									>
										<option value="">Select Role</option>
										<option value="Admin">Admin</option>
										<option value="Member">Member</option>
									</select>
									<span v-if="errors.role" class="text-red-500 text-sm">{{ errors.role[0] }}</span>
								</div>

								<div class="md:col-span-2" v-if="!(authUser?.role?.name === 'member' || authUser?.role?.name === 'admin')">
									<label class="block text-sm font-medium mb-2">Company Name</label>
									<input
										v-model="form.company_name"
										type="text"
										class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900"
										:class="{ 'border-red-500': errors.company_name }"
										placeholder="Company name"
									/>
									<span v-if="errors.company_name" class="text-red-500 text-sm">{{ errors.company_name[0] }}</span>
								</div>
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
									{{ loading ? 'Creating...' : 'Invite' }}
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>

		<!-- Clients Table - Full Width -->
		<div class="w-full bg-white shadow-sm">
			<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
				<h3 class="text-lg font-semibold mb-4 px-6 sm:px-0 text-gray-900">Clients List</h3>
				<div class="overflow-x-auto px-6 sm:px-0">
					<VueNiceTable
						:items="clients"
						:fields="tableFields"
						:busy="tableLoading"
						:striped="true"
						:hover="true"
						:bordered="true"
						responsive="lg"
						:sort-by="sortBy"
						:sort-desc="sortDesc"
						@sort-changed="handleSort"
						@row-clicked="editClient"
					>
						<template #table-busy>
							<div class="text-center py-4">Loading clients...</div>
						</template>

						<template #empty>
							<div class="text-center py-4">No clients found</div>
						</template>

						<template #cell_role="{ item }">
							<span class="px-2 py-1 text-xs font-semibold rounded-full"
								:class="item.role === 'Admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'">
								{{ item.role }}
							</span>
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

const clients = ref([]);
const loading = ref(false);
const tableLoading = ref(false);
const sortBy = ref('name');
const sortDesc = ref(false);

const errors = reactive({});

const form = reactive({
	name: '',
	email: '',
	password: '',
	role: '',
	company_name: '',
});

const tableFields = [
	{ key: 'name', label: 'Name', sortable: true },
	{ key: 'email', label: 'Email', sortable: true },
	{ key: 'role', label: 'Role', sortable: true },
	{ key: 'company_name', label: 'Company', sortable: true },
];

const resetForm = () => {
	form.name = '';
	form.email = '';
	form.password = '';
	form.role = '';
	form.company_name = '';
	Object.keys(errors).forEach(key => delete errors[key]);
};

const ensureCsrfCookie = async () => {
	// Natural Sanctum SPA flow: ensure XSRF cookie is set
	await window.axios.get('/sanctum/csrf-cookie');
};

const storeClient = async () => {
	loading.value = true;
	Object.keys(errors).forEach(key => delete errors[key]);

	try {
		await ensureCsrfCookie();

		const { data } = await window.axios.post('/api/clients', form);

		clients.value.push(data.data);
		resetForm();
		alert('Client added successfully!');
	} catch (error) {
		if (error.response && error.response.status === 422 && error.response.data?.errors) {
			Object.assign(errors, error.response.data.errors);
		} else {
			console.error('Error:', error);
			alert('Failed to add client');
		}
	} finally {
		loading.value = false;
	}
};

const editClient = (item) => {
	form.name = item.name;
	form.email = item.email;
	form.role = item.role;
	form.company_name = item.company_name;
	window.scrollTo({ top: 0, behavior: 'smooth' });
};

const handleSort = (sortByKey, sortDescValue) => {
	sortBy.value = sortByKey;
	sortDesc.value = sortDescValue;
};

const fetchClients = async () => {
	tableLoading.value = true;
	try {
		await ensureCsrfCookie();
		const response = await window.axios.get('/api/clients');
		clients.value = response.data;
	} catch (error) {
		alert('Failed to load clients');
	} finally {
		tableLoading.value = false;
	}
};

onMounted(() => {
	fetchClients();
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
