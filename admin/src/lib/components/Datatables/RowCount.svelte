<script lang="ts">
	import type { DataHandler } from '@vincjo/datatables';
	export let handler: DataHandler;
	const rowCount = handler.getRowCount();
</script>

<aside class="text-sm leading-8 mr-6">
	{#if $rowCount.total > 0}
		Showing <b>{$rowCount.start}</b>
		to <b>{$rowCount.end}</b>
		of <b>{$rowCount.total}</b> record{$rowCount.total != 1 ? 's' : ''}
	{:else}
		No entries found
	{/if}
</aside>