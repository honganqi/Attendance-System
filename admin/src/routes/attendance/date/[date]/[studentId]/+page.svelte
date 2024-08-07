<script>
    import { faSignIn, faSignOut } from "@fortawesome/free-solid-svg-icons";
	import Fa from "svelte-fa";

	export let data;
	const { student, logs } = data;
	const timeFormat = { hour: 'numeric', minute: 'numeric' };
</script>

<h2>{student.fullname}</h2>

{#if logs}
<ul>
	{#each logs as log}
		<li>
			<Fa icon={log.userAction == 1 ? faSignOut : faSignIn} class={`inline ${log.userAction == 0 ? 'text-success-700 dark:text-success-500' : 'text-error-500 dark:text-error-400'}`} />
			{new Date(log.timeEntry).toLocaleTimeString(undefined, timeFormat)}
		</li>
	{/each}
</ul>
{/if}