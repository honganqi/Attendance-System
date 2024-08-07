<script lang="ts">
	import type { DataHandler } from '@vincjo/datatables';
	export let handler: DataHandler;
	const rowsPerPage = handler.getRowsPerPage();
	const options = [5, 10, 20, 50, 100];
	const setRowsPerPage = () => {
		handler.setPage(1);
		handler.invalidate();
	};
</script>

<aside class="flex place-items-center">
	<small>Show</small>
	<select class="select ml-2 text-sm" bind:value={$rowsPerPage} on:change={setRowsPerPage}>
		{#each options as option}
			<option value={option}>
				{option}
			</option>
		{/each}
	</select>
</aside>