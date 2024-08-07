import { writable } from "svelte/store";
import { browser } from '$app/environment';

export const activeSiteSection = writable('');
export const currentPage = writable('');

// Session based theme store. Grabs the current theme from the current body.
export const storeTheme = writable(browser ? document.body.getAttribute('data-theme') ?? '' : 'skeleton');