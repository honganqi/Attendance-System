<script lang="ts">
	import '../app.postcss';
	import { goto } from '$app/navigation';
	import { AppShell, AppBar, LightSwitch, Avatar, Toast, ListBox, ListBoxItem, popup, Modal, initializeStores, Drawer  } from '@skeletonlabs/skeleton';

	// Floating UI for Popups
	import { computePosition, autoUpdate, flip, shift, offset, arrow } from '@floating-ui/dom';
	import { storePopup } from '@skeletonlabs/skeleton';
	import { currentPage } from '$lib/stores';

	import Fa from 'svelte-fa';
	import { faUser, faClock, faMoon } from '@fortawesome/free-solid-svg-icons'

	import { page } from '$app/stores';

	initializeStores();


	let sitePages = [
		{id: 'attendance', label: 'Attendance', icon: faClock },
		{id: 'students', label: 'Students', icon: faUser },
	]

	let user = {
		initials: 'AA',
		src: 'https://i.pravatar.cc/',
		width: 'w-12',
	}

	storePopup.set({ computePosition, autoUpdate, flip, shift, offset, arrow });

	const popupProfile = {
		// Represents the type of event that opens/closed the popup
		event: 'focus-click',
		// Matches the data-popup value on your popup element
		target: 'popupProfile',
		// Defines which side of your trigger the popup will appear
		placement: 'bottom',
	};
</script>

<Modal regionBody="overflow-auto"/>
<Drawer position="right" width="w-6/12" />

<!-- App Shell -->
<AppShell>
	<svelte:fragment slot="header">
		<!-- App Bar -->
		<AppBar>
			<svelte:fragment slot="lead"><a href="/" id="siteTitleHeader" class="text-3xl font-bold">Attendance</a></svelte:fragment>
			<svelte:fragment slot="trail">
				{#each sitePages as page}
				<a href={`/${page.id}`} class="btn btn-sm variant-ghost-primary hover:variant-filled-primary flex lg:hidden">{page.label}</a>
				{/each}
				<button type="button" use:popup={popupProfile} class="rounded-full bg-surface-300-600-token p-4">
					<Fa icon={faUser} fw />
				</button>
				<div class="card p-4 w-72 shadow-xl" data-popup="popupProfile">
					{#if $page.data.session}
						{#if $page.data.session.user?.image}
						<div class="flex items-center gap-x-2 font-bold"><Avatar {...user} /> {$page.data.session.user?.name ?? "User"}</div>
						{/if}
					{/if}
					<li class="flex justify-between items-center space-x-1"><div class="flex-initial"><Fa icon={faMoon} fw /> </div> <div class="flex-1">Dark Mode </div> <div><LightSwitch /></div></li>
					<!--
					<hr class="my-4">
					<div>
						<ul class="space-y-2">
							<li class="flex justify-between items-center space-x-1"><div class="flex-initial"><Fa icon={faBell} fw /> </div> <div class="flex-1">Notifications</div></li>
							<li class="flex justify-between items-center space-x-1"><div class="flex-initial"><Fa icon={faGear} fw /> </div> <div class="flex-1">Settings</div></li>
							<li class="flex justify-between items-center space-x-1"><div class="flex-initial"><Fa icon={faMoon} fw /> </div> <div class="flex-1">Dark Mode </div> <div><LightSwitch /></div></li>
							<hr>
							<li class=""><a href="/auth/signout" class="flex justify-between items-center space-x-1"><div class="flex-initial"><Fa icon={faRightFromBracket} fw /> </div> <div class="flex-1">Log Out </div></a></li>
						</ul>
					</div>
						
					-->
					<div class="arrow bg-surface-100-800-token" />
				</div>
			</svelte:fragment>
		</AppBar>
	</svelte:fragment>
	<svelte:fragment slot="sidebarLeft">
		<div class="hidden lg:block">
			<ListBox rounded="rounded-e">
				{#each sitePages as page}
					<ListBoxItem
					bind:group={$currentPage}
					name="currentPage"
					value={page.id}
					on:click={() => goto(`/${page.id}`)}
					>
						<div class="px-4 py-2">
							<Fa icon={page.icon} fw style="display: inline" />
							{page.label}
						</div>
					</ListBoxItem>
				{/each}
			</ListBox>
		</div>
	</svelte:fragment>	
	<!-- Router Slot (add .h-full to blank pages)-->
	<div class="container flex mx-auto justify-center">
		<div id="content" class="w-full px-8">
			<slot />
			<Toast />	
		</div>
	</div>
	<!-- ---- / ---- -->
</AppShell>