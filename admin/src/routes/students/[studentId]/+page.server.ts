import { fail, type Actions } from '@sveltejs/kit';
import type { PageServerLoad } from "./$types";
import { Student } from '$lib/classes/Student';

export const actions: Actions = {
	update: async ({ request, params }) => {
		const newData = Object.fromEntries(await request.formData());
		const update = new Student(params.studentId);
		const response = await update.updateRecord(newData);
		return {
			status: response?.status,
			message: response?.message,
			messageType: response?.messageType
		}
	},

	updateStatus: async ({ request, params }) => {
		try {
			const formData = Object.fromEntries(await request.formData());
			const newStatus = formData.status != '1' ? '1' : '0';
			const update = new Student(params.studentId);
			const response = await update.updateStatus(newStatus);
			return {
				status: response?.status,
				message: response?.message,
				messageType: response?.messageType
			}
		}
		catch (err) {

		}
	},

	delete: async ({ params }) => {
		try {
			const student = new Student(params.studentId);
			const response = await student.deleteRecord();
			return {
				status: response?.status,
				message: response?.message,
				messageType: response?.messageType
			}
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