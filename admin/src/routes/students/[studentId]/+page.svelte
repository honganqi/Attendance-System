<script lang="ts">
    import { enhance } from '$app/forms';
    import { goto } from '$app/navigation';
    import { faCakeCandles, faIdBadge, faIdCard, faUser, faVenusMars, faPhone } from '@fortawesome/free-solid-svg-icons';
    import { getModalStore, getToastStore, ProgressRadial, type ModalSettings } from '@skeletonlabs/skeleton';
    import Fa from 'svelte-fa';

    const toastStore = getToastStore();
	const modalStore = getModalStore();

    export let data;
    const { student } = data;
    student.birthdate = student.id != 'new' ? formatDate(student.birthdate) : '';

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
    let deleteForm: any;

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

    async function handleSubmit({ action }) {
        // handleSubmit.params = { formElement, formData, action, cancel, submitter }
		// `form` is the `<form>` element
    	// `data` is its `FormData` object
    	// `action` is the URL to which the form is posted
    	// `cancel()` will prevent the submission
		// `submitter` is the `HTMLElement` that caused the form to be submitted

		// any data validation can be done here if needed on client side and if it fails, cancel() should be called

        formIsWorking = true;

		return async ({ result, update }) => {
	      	// `result` is an `ActionResult` object
      		// `update` is a function which triggers the logic that would be triggered if this callback wasn't set
			if (result.type !== 'failure') {
				await update({ reset: false });
			}
            
            if (result.data && result.data.data) {
                if ('fullname' in result.data.data) {
                    student.fullname = result.data.data.fullname;
                }
                if ('status' in result.data.data) {
                    student.status = result.data.data.status;
                }
            }

			showFormResponse(result.data);
			formIsWorking = false;

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

    let formAction = student.id == 'new' ? '?/addnew' : '?/update';
</script>

{#if formIsWorking}
<div id="loadingOverlay">
	<ProgressRadial />
</div>
{/if}

<h1>{#if student.id == 'new'}New {/if}Student Details</h1>
{#if student.id != 'new'}<h2 class="mt-2">{student.fullname}</h2>{/if}

<form
method="POST"
action={formAction}
use:enhance={handleSubmit}
class="mt-4 space-y-8"
>
<div class="space-y-3">
    <div class="lg:flex gap-x-4">
        <label class="label">
            <span><Fa icon={faUser} class="inline" /> Student Name</span>
            <div class="lg:flex gap-x-4">
                <label class="label">
                    <input
                        type="text"
                        class="input"
                        name="lastname"
                        bind:value={student.lastname}
                    />
                    <span class="text-surface-500-400-token text-xs">Family Name</span>    
                </label>
                <label class="label">
                    <input
                        type="text"
                        class="input"
                        name="firstname"
                        bind:value={student.firstname}
                    />
                    <span class="text-surface-500-400-token text-xs">Given Name</span>    
                </label>
                <label class="label">
                    <input
                        type="text"
                        class="input"
                        name="middlename"
                        bind:value={student.middlename}
                    />
                    <span class="text-surface-500-400-token text-xs">Middle Name</span>    
                </label>
                <label class="label">
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
                    <span class="text-surface-500-400-token text-xs">Suffix</span>  
                </label>
            </div>
        </label>
    </div>
    
    <div class="lg:flex gap-x-4">
        <label class="label">
            <span><Fa icon={faIdCard} class="inline" /> Nickname</span>
            <input type="text" class="input" bind:value={student.nickname} name="nickname" placeholder="Nickname" required />
        </label>    
    </div>
    
    <div class="lg:flex gap-x-4 lg:space-y-0 space-y-3">
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
</div>

<div>
    <div class="space-y-3">
        <div class="lg:flex gap-x-4">
            <label class="label">
                <span><Fa icon={faPhone} class="inline" /> Contact in Case of Emergency</span>
                <div class="lg:flex gap-x-4">
                    <div class="">
                        <label class="label">
                            <input
                                type="text"
                                class="input"
                                name="emergencyContact"
                                bind:value={student.emergencyContact}
                            />
                            <span class="text-surface-500-400-token text-xs">Name</span>    
                        </label>
                    </div>
                    <div class="">
                        <label class="label">
                            <input
                                type="text"
                                class="input"
                                name="emergencyNumber"
                                bind:value={student.emergencyNumber}
                            />
                            <span class="text-surface-500-400-token text-xs">Contact Number</span>    
                        </label>
                    </div>
                    <div class="">
                        <label class="label">
                            <input
                                type="text"
                                class="input"
                                name="emergencyRelationship"
                                bind:value={student.emergencyRelationship}
                            />
                            <span class="text-surface-500-400-token text-xs">Relationship to Student</span>    
                        </label>
                    </div>
                </div>
            </label>
        </div>    
    </div>    
</div>

<div>
    <div class="lg:flex gap-x-4">
        <label class="label">
            <span><Fa icon={faIdBadge} class="inline" /> NFC ID Number</span>
            <input type="text" class="input" bind:value={student.idnumber} name="idnumber" placeholder="NFC ID Number" />
        </label>    
    </div>    
</div>


<input type="hidden" name="studentId" value={student.id} />
<input type="submit" class="btn h-12 bg-primary-300-600-token cursor-pointer" value="Save" />
</form>


{#if student.id != 'new'}
<form
method="POST"
action="?/updateStatus"
class="mt-12 space-y-3"
use:enhance={handleSubmit}
>
<input type="hidden" name="status" value={student.status} />
Status <span class="chip cursor-default {student.status ? `variant-filled-success` : `variant-filled-error`}">{student.status ? 'ACTIVE' : 'INACTIVE'}</span>
<p><input type="submit" class={`btn btn-sm variant-ghost-surface ${student.status ? 'hover:variant-ghost-error' : 'hover:variant-ghost-success'} cursor-pointer`} value={`Set status as ${student.status ? 'INACTIVE' : 'ACTIVE'}`} /></p>
</form>


<form
method="POST"
action="?/delete"
class="mt-16 space-y-3"
bind:this={deleteForm}
use:enhance={handleSubmit}
>
<button type="button" class="btn btn-sm variant-ringed-error hover:variant-filled-error cursor-pointer" on:click={deleteRecord}>Delete record</button>
</form>
{/if}