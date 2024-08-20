import { fail, type Actions } from '@sveltejs/kit';
import type { PageServerLoad } from "./$types";
import { Student } from '$lib/classes/Student';
import { Response } from '$lib/classes/Response';

export const actions: Actions = {
	addnew: async ({ request }) => {
		const newData = Object.fromEntries(await request.formData());
		const student = new Student();
		const response = await student.createRecord(newData);
		
		return Response.sendResponse(response);
	},

	update: async ({ request, params }) => {
		const newData = Object.fromEntries(await request.formData());
		const student = new Student(params.studentId);
		const response = await student.updateRecord(newData);

		return Response.sendResponse(response);
	},

	updateStatus: async ({ request, params }) => {
		try {
			const formData = Object.fromEntries(await request.formData());
			const newStatus = formData.status != 'true' ? true : false;
			const student = new Student(params.studentId);
			const response = await student.updateStatus(newStatus);

			return Response.sendResponse(response);
		}
		catch (err) {

		}
	},

	delete: async ({ params }) => {
		try {
			const student = new Student(params.studentId);
			const response = await student.deleteRecord();
			
			return Response.sendResponse(response);
		}
		catch (err) {

		}
	}
};

/** @type {import('@sveltejs/kit').Load} */
export const load: PageServerLoad = async ({ locals, params }) => {
	const student = new Student(params.studentId);
	const data = await student.getRecord();

	return {
		student: data
	};
};