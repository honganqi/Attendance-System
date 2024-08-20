import type { PageServerLoad } from "./$types";
import { Student } from '$lib/classes/Student';

/** @type {import('@sveltejs/kit').Load} */
export const load: PageServerLoad = async ({ url }) => {
	let students = [];
	const inactive = url.searchParams.get('inactive') !== null;
	const response = new Student();
	const data = await response.getList(inactive);
	if (data) {
		students = data;
	}
	return {
		students,
		inactive
	}
};