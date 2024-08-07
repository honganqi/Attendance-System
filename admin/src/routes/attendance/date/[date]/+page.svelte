<script>
	import { goto } from '$app/navigation';
	import { DataHandler } from '@vincjo/datatables';
	import { ThFilter, ThSort, RowCount, RowsPerPage, Pagination } from '$lib/components/Datatables';

	const timeFormat = { hour: 'numeric', minute: 'numeric', second: 'numeric' };
	
	export let data;
	let logs = [];
	$: ({ logs } = data);

	const handler = new DataHandler(logs, { rowsPerPage: 20 });
	const rows = handler.getRows();
	$: logs, handler.setRows(logs);
	
	function loadRecord(student) {
		const studentId = student.id;
		goto(`/attendance/date/${data.urlDate}/${studentId}`);
	}
</script>


<div class="table-container space-y-4">
    <header class="flex justify-between gap-4">
		<div></div>
		<RowsPerPage {handler} />
	</header>    
    <table class="table table-hover table-compact table-auto w-full">
		<thead>
			<tr>
				<ThSort {handler} orderBy="nickname">Name</ThSort>
				<ThSort {handler} orderBy="fullname">Full Name</ThSort>
				<ThSort {handler} orderBy="time_in">In</ThSort>
				<ThSort {handler} orderBy="time_out">Out</ThSort>
			</tr>
			<tr>
				<ThFilter {handler} filterBy="nickname" />
				<ThFilter {handler} filterBy="fullname" />
				<th></th>
				<th></th>
			</tr>
		</thead>
        <tbody>
            {#each $rows as row}
                <tr on:click={loadRecord(row)}>
					<td>{row.name}</td>
					<td>{row.fullname}</td>
					<td>{new Date(row.in).toLocaleTimeString(undefined, timeFormat)}</td>
					<td>{new Date(row.out).toLocaleTimeString(undefined, timeFormat)}</td>
				</tr>
            {/each}
        </tbody>
    </table>
    <footer class="flex justify-between">
		<RowCount {handler} />
		<Pagination {handler} />
	</footer>
</div>