/**
 * a store that's supposed to say whether the content to be loaded has "items-center" in its container class
 */

import { writable } from "svelte/store";

export const activeSiteSection = writable('');
export const currentPage = writable('');