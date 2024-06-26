import { getToastStore } from "@skeletonlabs/skeleton";

/**
 * 
 * @param {string} message 
 * @param {'primary' | 'secondary' | 'tertiary' | 'warning' | 'success' | 'error'} preset 
 */
export function toastTrigger(message, preset) {
	const toastStore = getToastStore();
	toastStore.trigger({
		message: message,
		background: preset,
	});
}
