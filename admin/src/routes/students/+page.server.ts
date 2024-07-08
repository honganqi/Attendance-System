import { fail, type Actions } from '@sveltejs/kit';
import type { PageServerLoad } from "./$types";
import { Student } from '$lib/classes/Student';

export const actions: Actions = {
	addnew: async ({ request }) => {
		const newData = Object.fromEntries(await request.formData());
		const student = new Student();
		const response = await student.createRecord(newData);
		return {
			status: response?.status,
			message: response?.message,
			messageType: response?.messageType
		}
		
	}

};

/** @type {import('@sveltejs/kit').Load} */
export const load: PageServerLoad = async ({ url }) => {
	let students = [];
	const inactive = url.searchParams.get('inactive') !== null;
	const response = new Student();
	const data = await response.getList(inactive ? "only" : "");
	if (data) {
		students = data;
	}
	return {
		students,
		inactive
	}
};