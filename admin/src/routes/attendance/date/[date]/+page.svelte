<script>
    import { Table, tableMapperValues } from '@skeletonlabs/skeleton';

	const timeFormat = { hour: 'numeric', minute: 'numeric', second: 'numeric' };
	
	export let data;
	let logs = [];
    $: ({ logs } = data);

	let tableContents;
	tableContents = {
		head: ['Name', 'Full Name', 'In', 'Out'],
		body: [],
	}
	$: {
		logs.map(logItem => {
			logItem.in_out = logItem.in_out == 0 ? 'in' : 'out';
			logItem.in = new Date(logItem.in).toLocaleTimeString(undefined, timeFormat);
			logItem.out = logItem.out != null ? new Date(logItem.out).toLocaleTimeString(undefined, timeFormat) : ''
		});

		tableContents.body = tableMapperValues(logs, ['name', 'fullname', 'in', 'out']);
	}
</script>

<Table source={tableContents} interactive={true} />
