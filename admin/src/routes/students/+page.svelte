 <script lang="ts">
	import { goto } from '$app/navigation';
	import Fa from 'svelte-fa';
	import { faPlus } from '@fortawesome/free-solid-svg-icons';
    import { DataHandler } from '@vincjo/datatables';
    import { Search, ThFilter, ThSort, RowCount, RowsPerPage, Pagination } from '$lib/components/Datatables';

	export let data;
	$: students = data.students;

	const handler = new DataHandler(students, { rowsPerPage: 20 });
	const rows = handler.getRows();
	$: data, handler.setRows(students);

	function loadRecord(student: any) {
		const studentId = student.id;
		goto(`/students/${studentId}`);
	}
</script>

<div class="flex justify-between mb-4 items-center">
	<h1>Students</h1>
	<a href="/students/new" class="btn btn-sm h-12 bg-secondary-300-600-token"><Fa icon={faPlus} class="mr-2" /> Add student</a>
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
