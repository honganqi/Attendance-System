<script lang="ts">
	import '../app.postcss';
	import { goto } from '$app/navigation';
	import { LightSwitch, Avatar, Toast, ListBox, ListBoxItem, popup, Modal, initializeStores, Drawer, getDrawerStore, type DrawerSettings } from '@skeletonlabs/skeleton';

	// Floating UI for Popups
	import { computePosition, autoUpdate, flip, shift, offset, arrow } from '@floating-ui/dom';
	import { storePopup, modeCurrent as lightMode } from '@skeletonlabs/skeleton';
	import { currentPage } from '$lib/stores';

	import Fa from 'svelte-fa';
	import { faUser, faClock, faMoon } from '@fortawesome/free-solid-svg-icons'

	import { page } from '$app/stores';
	import { storeTheme } from '$lib/stores';
	import { browser } from '$app/environment';

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

	const drawerStore = getDrawerStore();

	storePopup.set({ computePosition, autoUpdate, flip, shift, offset, arrow });

	const popupProfile = {
		// Represents the type of event that opens/closed the popup
		event: 'click',
		// Matches the data-popup value on your popup element
		target: 'popupProfile',
		// Defines which side of your trigger the popup will appear
		placement: 'bottom-end',
	};

	function launchNav() {
		const drawerSettings: DrawerSettings = {
			id: 'navDrawer',
			width: 'w-9/12',
			position: 'left'
		}
		drawerStore.open(drawerSettings);
	}

	// Set body `data-theme` based on current theme status
	storeTheme.subscribe(setBodyThemeAttribute);
	function setBodyThemeAttribute(): void {
		if (!browser) return;
		document.body.setAttribute('data-theme', $storeTheme);
	}

	const themes = [
		{ type: 'skeleton', name: 'Skeleton', icon: '💀' },
		{ type: 'wintry', name: 'Wintry', icon: '🌨️' },
		{ type: 'modern', name: 'Modern', icon: '🤖' },
		{ type: 'rocket', name: 'Rocket', icon: '🚀' },
		{ type: 'seafoam', name: 'Seafoam', icon: '🧜‍♀️' },
		{ type: 'vintage', name: 'Vintage', icon: '📺' },
		{ type: 'sahara', name: 'Sahara', icon: '🏜️' },
		{ type: 'hamlindigo', name: 'Hamlindigo', icon: '👔' },
		{ type: 'gold-nouveau', name: 'Gold Nouveau', icon: '💫' },
		{ type: 'crimson', name: 'Crimson', icon: '⭕' }
	];

	let theme: string;
	$: {
		if (theme != $storeTheme) {
			setTheme();
		}
	}

	const setTheme = () => {
		if (theme) {
			$storeTheme = theme;
			document.body.setAttribute('data-theme', theme);
		}
	};

	import { logoHeader, logoHeaderDark } from '$lib/config';
	let logoPath: string;
	$: logoPath = $lightMode ? logoHeader : logoHeaderDark;
</script>

<Modal regionBody="overflow-auto"/>
<Drawer>
	<div class="p-4">
		<a href="/" id="siteTitleHeader" class="text-3xl font-bold ml-2 lg:ml-0">
			{#if logoPath }
			<img src={logoPath} alt="Attendance" style="max-height: 35px;"/>
			{:else}
			Attendance
			{/if}
		</a>	
	</div>
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
</Drawer>

<div class="grid h-screen grid-rows-[auto_1fr_auto]">
	<!-- Header -->
	<header class="bg-surface-100-800-token p-4 grid items-center grid-cols-[1fr_auto] gap-4">
		<div>
			<button class="lg:hidden btn btn-sm p-0" on:click={launchNav}>
				<span>
					<svg viewBox="0 0 100 80" class="fill-token w-4 h-4">
						<rect width="100" height="20" />
						<rect y="30" width="100" height="20" />
						<rect y="60" width="100" height="20" />
					</svg>
				</span>
			</button>
			<a href="/" id="siteTitleHeader" class="text-3xl font-bold ml-2 lg:ml-0">
				{#if logoPath }
				<img src={logoPath} alt="Attendance" style="max-height: 35px;"/>
				{:else}
				Attendance
				{/if}
			</a>
		</div>
		<div>
			<div class="hidden lg:block">
				{#each sitePages as page}
				<a href={`/${page.id}`} class="btn btn-sm variant-ghost-primary hover:variant-filled-primary flex lg:hidden">{page.label}</a>
				{/each}
			</div>
			<button type="button" use:popup={popupProfile} class="rounded-full bg-surface-300-600-token p-4">
				<Fa icon={faUser} fw />
			</button>
			<div class="bg-surface-200-700-token  card p-4 shadow-xl" data-popup="popupProfile">
				{#if $page.data.session}
					{#if $page.data.session.user?.image}
					<div class="flex items-center gap-x-2 font-bold"><Avatar {...user} /> {$page.data.session.user?.name ?? "User"}</div>
					{/if}
				{/if}
				<div class="space-y-4 lg:space-y-2">
					<div>
						Theme
						<select class="input" bind:value={theme}>
							<!-- , badge -->
							{#each themes as { icon, name, type }}
								<option value={type}>{icon} {name}</option>
							{/each}
						</select>
					</div>

					<ul class="space-y-2">
						<!-- <li class="flex justify-between items-center space-x-1"><div class="flex-initial"><Fa icon={faBell} fw /> </div> <div class="flex-1">Notifications</div></li> -->
						<!-- <li class="flex justify-between items-center space-x-1"><div class="flex-initial"><Fa icon={faGear} fw /> </div> <div class="flex-1">Settings</div></li> -->
						<li class="flex justify-between items-center space-x-1"><div class="flex-initial"><Fa icon={faMoon} fw /> </div> <div class="flex-1">Dark Mode </div> <div><LightSwitch /></div></li>
						<!-- <hr> -->
						<!-- <li class=""><a href="/auth/signout" class="flex justify-between items-center space-x-1"><div class="flex-initial"><Fa icon={faRightFromBracket} fw /> </div> <div class="flex-1">Log Out </div></a></li> -->
					</ul>
				</div>
				<div class="arrow bg-surface-200-700-token" />
			</div>			
		</div>
	</header>
	<!-- Page -->
	<div class="grid grid-cols-1 md:grid-cols-[auto_1fr]">
		<!-- Sidebar (Left) -->
		<!-- NOTE: hidden in smaller screen sizes -->
		<aside class="sticky hidden lg:block">
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
		</aside>
		<!-- Main -->
		<main class="">
		<div class="w-full px-8 py-4">
			<slot />
			<Toast />	
		</div>
		</main>
	</div>	
</div>
