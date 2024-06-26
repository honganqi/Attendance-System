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
		
		// const { lastname, firstname, middlename, suffix, nickname, birthdate, gender } = Object.fromEntries(await request.formData()) as {
		// 	lastname: string;
		// 	firstname: string,
		// 	middlename: string;
		// 	suffix: string;
		// 	nickname: string;
		// 	birthdate: string;
		// 	gender: string;
		// };

		// if (!lastname && !firstname && !nickname && (gender !== "male" && gender !== "female")) {
		// 	return fail(400, {message: "Missing required parameters"})
		// }
		
		// try {
		// 	const student = await prisma.students.create({
		// 		data: {
		// 			lastname,
		// 			firstname,
		// 			middlename,
		// 			suffix,
		// 			nickname,
		// 			birthdate: new Date(birthdate),
		// 			gender,
		// 			status: true
		// 		}
		// 	})

		// 	return {
		// 		status: 201,
		// 		message: 'New student added successfully!',
		// 		messageType: 'variant-filled-success'
		// 	}
		// } catch (err) {
		// 	console.error('Failed to create event. Err: ' + err);
		// 	return fail(422, { message: 'Event creation failed' });
		// }

		// return {
		// 	status: 201
		// };
	}
};

/** @type {import('@sveltejs/kit').Load} */
export const load: PageServerLoad = async () => {
	let students = [];
	const response = new Student();
	const data = await response.getList();
	if (data) {
		students = data;
	}
	return {
		students,
		status: response?.status,
		message: response?.message,
		messageType: response?.messageType
	}
};