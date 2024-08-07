<script lang="ts">
	import type { DataHandler } from '@vincjo/datatables';
	import Fa from 'svelte-fa';
	import { faSort, faSortUp, faSortDown } from '@fortawesome/free-solid-svg-icons'

	export let handler: DataHandler;
	export let orderBy: string;

	const identifier = orderBy?.toString()
	const sorted = handler.getSort();
</script>

<th on:click={() => handler.sort(orderBy)} class="cursor-pointer select-none p-2 px-5">
	<div class="flex h-full items-center justify-start gap-x-2">
		<slot />
		{#if $sorted.identifier === identifier}
			{#if $sorted.direction === "asc"}
				<Fa icon={faSortUp} />
			{:else}
				<Fa icon={faSortDown} />
			{/if}
		{:else}
			<Fa icon={faSort} />
		{/if}
	</div>
</th>

<style>

</style>