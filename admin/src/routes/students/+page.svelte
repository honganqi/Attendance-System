 <script lang="ts">
    import { Table, tableMapperValues, Drawer, getDrawerStore, getToastStore, type AutocompleteOption, Autocomplete, type PopupSettings, popup, ProgressRadial, Paginator, type PaginationSettings } from '@skeletonlabs/skeleton';
	import { enhance } from '$app/forms';
	import Fa from 'svelte-fa';
	import { faPlus } from '@fortawesome/free-solid-svg-icons';
	import { goto } from '$app/navigation';

	const toastStore = getToastStore();
	const drawerStore = getDrawerStore();
	
	const drawerSettings = {
		id: 'newStudentDrawer',
		width: 'w-[280px] md:w-[480px]',
	};

	export let data;
	$: students = data.students;

	let formIsWorking = false;

	let tableContents;
	tableContents = {
		head: ['Name', 'Nickname'],
		body: [],
		meta: students
	}
	$: {
		tableContents.body = tableMapperValues(students, ['fullname', 'nickname']);
		tableContents.meta = students;
	}

	let paginationSettings = {
		page: 0,
		limit: 25,
		size: 0,
		amounts: [10,25,50,100],
	} satisfies PaginationSettings;

	$: paginationSettings.size = students.length;


	async function handleSubmit({ formElement, formData, action, cancel, submitter }) {
		formIsWorking = true;
		// `form` is the `<form>` element
    	// `data` is its `FormData` object
    	// `action` is the URL to which the form is posted
    	// `cancel()` will prevent the submission
		// `submitter` is the `HTMLElement` that caused the form to be submitted

		// any data validation can be done here if needed on client side and if it fails, cancel() should be called
		// formData.set('eventId', eventId);

		return async ({ result, update }) => {
	      	// `result` is an `ActionResult` object
      		// `update` is a function which triggers the logic that would be triggered if this callback wasn't set
			if (result.type !== 'failure') {
				await update({ reset: false });
			}
			formIsWorking = false;
			drawerStore.close();
			showFormResponse(result.data);
    	};
	}

	function loadRecord(student) {
		const studentId = student.detail.id;
		goto(`/students/${studentId}`);
	}

	function getDrawer() {
		drawerStore.open(drawerSettings);
	}

	function showFormResponse(e) {
		toastStore.trigger({
			message: e.message,
			background: e.messageType,
		});
	}
</script>

<Drawer position="right" width="w-6/12">
	<div class="pt-8 px-8 space-y-4">
		<h2>New Student Details</h2>
		<form
			method="POST"
			action="?/addnew"
			use:enhance={handleSubmit}
			class="space-y-3"
		>
			<input type="text" class="input" name="lastname" placeholder="Family Name" required />
			<input type="text" class="input" name="firstname" placeholder="Given Name" required />
			<input type="text" class="input" name="middlename" placeholder="Middle Name" />
			<select class="input" name="suffix" required>
				<option value="" disabled selected>Suffix</option>
				<option value="">no suffix</option>
				{#each ['Jr.', 'Sr.', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'] as suffix}
				<option value={suffix}>{suffix}</option>
				{/each}
			</select>
			<input type="text" class="input" name="nickname" placeholder="Nickname" required />
			<input type="date" class="input" name="birthdate" placeholder="Date of Birth" required />
			<select class="input" name="gender" required>
				<option value="" disabled selected>Gender</option>
				<option value="male">Male</option>
				<option value="female">Female</option>
			</select>
			<input type="hidden" name="studentId" value="new" />
			<input type="submit" class="btn h-12 bg-primary-300-600-token" value="Save" />
		</form>
	
	</div>
</Drawer>

{#if formIsWorking}
<div id="loadingOverlay">
	<ProgressRadial />
</div>
{/if}

<div class="flex justify-between mb-4 items-center">
	<h1>Students</h1>
	<button type="button" class="btn btn-sm h-12 bg-secondary-300-600-token" on:click={getDrawer}><Fa icon={faPlus} class="mr-2" /> Add student</button>
</div>

<small>{students.length} {data.inactive ? 'inactive' : 'active'} record{students.length == 1 ? '' : 's'}</small>

<Table source={tableContents} interactive={true} on:selected={loadRecord} />

{#if students.length > 0}
<Paginator bind:settings={paginationSettings} class="mt-5" />
{/if}

<a
	href={`/students${data.inactive ? '' : '?inactive'}`}
	class={`btn btn-sm h-12 mt-12 ${data.inactive ? 'variant-ghost-success' : 'variant-ghost-error'}`}
	>
	View {data.inactive ? "active" : "inactive"} records
</a>
