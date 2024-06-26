<script lang="ts">
    import { enhance } from '$app/forms';
    import { goto } from '$app/navigation';
    import { faCakeCandles, faIdBadge, faIdCard, faUser, faVenusMars } from '@fortawesome/free-solid-svg-icons';
    import { getModalStore, getToastStore, type ModalSettings } from '@skeletonlabs/skeleton';
    import Fa from 'svelte-fa';

    const toastStore = getToastStore();
	const modalStore = getModalStore();

    export let data;
    const student = JSON.parse(data.student);
    student.birthdate = formatDate(student.birthdate);

    function formatDate(date) {
        date = new Date(date);
		let month = '' + (date.getMonth() + 1);
		let day = '' + date.getDate();
		let year = date.getFullYear();

		if (month.length < 2) 
			month = '0' + month;
		if (day.length < 2) 
			day = '0' + day;

		return [year, month, day].join('-');
	};

    let formIsWorking = false;
    let deleteForm;

	function deleteModal(): void {
        const modal: ModalSettings = {
            type: 'prompt',
            // Data
            title: 'Confirm Delete',
            body: 'Are you sure? Type <strong>"DELETE"</strong> to proceed.',
            value: '',
            valueAttr: { type: 'text' },
            response: (r) => {
                if (r) {
                    if (r == "DELETE") {
                        deleteForm.requestSubmit();
                    }
                }
            },
        };
        modalStore.trigger(modal);
	}

    function deleteRecord() {
        deleteModal();
    }

    async function handleSubmit({ formElement, formData, action, cancel, submitter }) {
		formIsWorking = true;
		// `form` is the `<form>` element
    	// `data` is its `FormData` object
    	// `action` is the URL to which the form is posted
    	// `cancel()` will prevent the submission
		// `submitter` is the `HTMLElement` that caused the form to be submitted

		// any data validation can be done here if needed on client side and if it fails, cancel() should be called
		// formData.set('eventId', eventId);

		return async ({ result, update }) => {
	      	// `result` is an `ActionResult` object
      		// `update` is a function which triggers the logic that would be triggered if this callback wasn't set
			if (result.type !== 'failure') {
				await update({ reset: false });
			}
			formIsWorking = false;
			showFormResponse(result.data);

            if (action.search == '?/delete') {
                goto('/students');
            }
    	};
	}

    function showFormResponse(e) {
		toastStore.trigger({
			message: e.message,
			background: e.messageType,
		});
	}
</script>

<h1>Student Details</h1>
<h2 class="mt-2">{student.fullname}</h2>

<form
method="POST"
action="?/update"
use:enhance={handleSubmit}
class="mt-4 space-y-3"
>

<label class="label">
    <span><Fa icon={faUser} class="inline" /> Student Name</span>
    <div class="lg:flex gap-x-4">
        <div class="">
            <label class="label">
                <input
                    type="text"
                    class="input"
                    name="lastname"
                    bind:value={student.lastname}
                />
                <span class="text-surface-500-400-token text-xs">Family Name</span>    
            </label>
        </div>
        <div class="">
            <label class="label">
                <input
                    type="text"
                    class="input"
                    name="firstname"
                    bind:value={student.firstname}
                />
                <span class="text-surface-500-400-token text-xs">Given Name</span>    
            </label>
        </div>
        <div class="">
            <label class="label">
                <input
                    type="text"
                    class="input"
                    name="middlename"
                    bind:value={student.middlename}
                />
                <span class="text-surface-500-400-token text-xs">Middle Name</span>    
            </label>
        </div>
        <div class="">
            <select
            class="input"
            name="suffix"
            bind:value={student.suffix}
        >
        <option value="">no suffix</option>
        {#each ['Jr.', 'Sr.', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'] as suffix}
        <option value={suffix}>{suffix}</option>
        {/each}
        </select>
        </div>
    </div>
    
</label>

<div class="lg:flex gap-x-4">
    <label class="label">
        <span><Fa icon={faIdCard} class="inline" /> Nickname</span>
        <input type="text" class="input" bind:value={student.nickname} name="nickname" placeholder="Nickname" required />
    </label>    
</div>

<div class="lg:flex gap-x-4">
    <label class="label">
        <span><Fa icon={faCakeCandles} class="inline" /> Date of Birth</span>
        <input type="date" class="input" bind:value={student.birthdate} name="birthdate" placeholder="Date of Birth" required />
    </label>
    
    <label class="label">
        <span><Fa icon={faVenusMars} class="inline" /> Gender</span>
        <select class="input" bind:value={student.gender} name="gender" required>
            <option value="" disabled selected>Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
        </select>
    </label>
    
</div>

<div class="lg:flex gap-x-4">
    <label class="label">
        <span><Fa icon={faIdBadge} class="inline" /> NFC ID Number</span>
        <input type="text" class="input" bind:value={student.idnumber} name="idnumber" placeholder="NFC ID Number" />
    </label>    
</div>


<input type="hidden" name="studentId" value={student.id} />
<input type="submit" class="btn h-12 bg-primary-300-600-token cursor-pointer" value="Save" />
</form>




<form
method="POST"
action="?/updateStatus"
class="mt-12 space-y-3"
>
<input type="hidden" name="status" value={student.status} />
Status: {student.status ? 'ACTIVE' : 'INACTIVE'} <input type="submit" class={`btn btn-sm ${student.status ? 'variant-ghost-error' : 'variant-ghost-success'} cursor-pointer`} value={`Set status as ${student.status ? 'INACTIVE' : 'ACTIVE'}`} />
</form>


<form
method="POST"
action="?/delete"
class="mt-16 space-y-3"
bind:this={deleteForm}
use:enhance={handleSubmit}
>
<button type="button" class="btn btn-sm variant-ghost-error cursor-pointer" on:click={deleteRecord}>Delete record</button>
</form>