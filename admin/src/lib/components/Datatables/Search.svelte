<script lang="ts">
	import type { DataHandler } from '@vincjo/datatables';
	export let handler: DataHandler;
	let value: string;
	let timeout: any;
</script>

<input
	class="input-variant-secondary input sm:w-64 w-36"
	type="search"
	placeholder="Search..."
	bind:value
	on:input={() => handler.search(value)}
/>