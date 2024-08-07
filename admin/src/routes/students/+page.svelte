 <script lang="ts">
	import { goto } from '$app/navigation';
    import { Drawer, getDrawerStore, getToastStore, ProgressRadial } from '@skeletonlabs/skeleton';
	import { enhance } from '$app/forms';
	import Fa from 'svelte-fa';
	import { faPlus } from '@fortawesome/free-solid-svg-icons';
    import { DataHandler } from '@vincjo/datatables';
    import { Search, ThFilter, ThSort, RowCount, RowsPerPage, Pagination } from '$lib/components/Datatables';

	const toastStore = getToastStore();
	const drawerStore = getDrawerStore();
	
	const drawerSettings = {
		id: 'newStudentDrawer',
		width: 'w-[280px] md:w-[480px]',
	};

	export let data;
	$: students = data.students;

	const handler = new DataHandler(students, { rowsPerPage: 20 });
	const rows = handler.getRows();
	$: data, handler.setRows(students);

	function loadRecord(student) {
		const studentId = student.id;
		goto(`/students/${studentId}`);
	}

	let formIsWorking = false;

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


<div class="table-container space-y-4">
    <header class="flex justify-between gap-4">
		<Search {handler} />
		<RowsPerPage {handler} />
	</header>    
    <table class="table table-hover table-compact table-auto w-full">
		<thead>
			<tr>
				<ThSort {handler} orderBy="fullname">Name</ThSort>
				<ThSort {handler} orderBy="nickname">Nickname</ThSort>
			</tr>
			<tr>
				<ThFilter {handler} filterBy="fullname" />
				<ThFilter {handler} filterBy="nickname" />
			</tr>
		</thead>
        <tbody>
            {#each $rows as row}
                <tr on:click={loadRecord(row)}>
					<td>{row.fullname}</td>
					<td>{row.nickname}</td>
				</tr>
            {/each}
        </tbody>
    </table>
    <footer class="flex justify-between">
		<RowCount {handler} />
		<Pagination {handler} />
	</footer>
</div>
    
<a
	href={`/students${data.inactive ? '' : '?inactive'}`}
	class={`btn btn-sm h-12 mt-12 ${data.inactive ? 'variant-ghost-success' : 'variant-ghost-error'}`}
	>
	View {data.inactive ? "active" : "inactive"} records
</a>
