<script>
    import { ProgressRadial } from '@skeletonlabs/skeleton';
	import Fa from 'svelte-fa';
	import { faCalendar } from '@fortawesome/free-solid-svg-icons';

	import { DatePicker } from '@svelte-plugins/datepicker';
    import { goto } from '$app/navigation';
    import { onMount } from 'svelte';
    import { page } from '$app/stores';

	const headerDateFormat = { month: 'long', day: 'numeric', year: 'numeric', weekday: 'long' };

    export let data;
    let selectedDate = data.selectedDate;
	let displayDate = new Date(selectedDate);
	let isOpen = false;
	const toggleDatePicker = () => (isOpen = !isOpen);
	const formatDate = (dateString) => {
		return dateString && (new Date(dateString).toLocaleDateString(undefined, headerDateFormat)) || '';
	};
	let formattedDisplayDate = formatDate(displayDate)
	$: {
        formattedDisplayDate = formatDate(displayDate)
        let newDate = new Date(displayDate);
        let year = newDate.getFullYear() + "";
        let month = (newDate.getMonth() + 1) + "";
        if (month.length == 1) {
            month = "0" + month;
        }
        let date = newDate.getDate() + "";
        if (date.length == 1) {
            date = "0" + date;
        }
        let dateString = year + month + date;
        let newURL = `/attendance/date/${dateString}`;
        if ($page.url.pathname != newURL) {
            goto(`/attendance/date/${dateString}`);
        }
    }
	const onChange = () => {
		displayDate = new Date(formattedDisplayDate)
	};

	let formIsWorking = false;
</script>

{#if formIsWorking}
<div id="loadingOverlay">
	<ProgressRadial />
</div>
{/if}

<div class="flex justify-between mb-4 items-center">
	<h1>Attendance</h1>
</div>

<DatePicker bind:isOpen bind:startDate={displayDate}>
    <button class="btn btn-sm chip variant-filled" on:click={toggleDatePicker}>
        <Fa icon={faCalendar} class="mr-2" />
        {formattedDisplayDate}
    </button>
    <input type="hidden" placeholder="Select date" bind:value={formattedDisplayDate} />
</DatePicker>

<slot />