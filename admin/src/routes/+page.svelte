<script lang="ts">
	import Fa from 'svelte-fa';
	import { faUser, faClock } from '@fortawesome/free-solid-svg-icons'
</script>

<div class="container h-full mx-auto flex justify-center items-center">
	<div class="space-y-6 text-center flex flex-col items-center">
		<img src="/img/logo.png" alt="logo">
		<h2 class="h2">Attendance System</h2>
		<div class="w-full text-token grid grid-cols-1 md:grid-cols-2 gap-4">
			<a href="/attendance" class="card p-4 hover:border-primary-500 border-transparent"><Fa icon={faClock} size="4x" class="w-full mb-3" /> Attendance</a>
			<a href="/students" class="card p-4 hover:border-primary-500 border-transparent"><Fa icon={faUser} size="4x" class="w-full mb-3" /> Students</a>
		</div>
	</div>
</div>

<style>
	.card {
		border-width: 2px;
		border-style: solid;
		transition-property: border-color;
		transition-duration: 300ms;
	}
</style>