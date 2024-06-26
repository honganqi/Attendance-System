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
		/*
		const { lastname, firstname, middlename, suffix, nickname, birthdate, gender } = Object.fromEntries(await request.formData()) as {
			lastname: string;
			firstname: string,
			middlename: string;
			suffix: string;
			nickname: string;
			birthdate: string;
			gender: string;
		};

		if (!lastname && !firstname && !nickname && (gender !== "male" && gender !== "female")) {
			return fail(400, {message: "Missing required parameters"})
		}
		
		try {
			const student = await prisma.students.update({
				where: {
					id: params.studentId,
				},
				data: {
					lastname,
					firstname,
					middlename,
					suffix,
					nickname,
					birthdate: new Date(birthdate),
					gender
				}
			})

			return {
				status: 201,
				message: 'Student details updated successfully!',
				messageType: 'variant-filled-success'
			}
		} catch (err) {
			console.error('Failed to create event. Err: ' + err);
			return fail(422, { message: 'Event creation failed' });
		}
		*/

		return {
			status: 201
		};
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
	await student.getRecord();

	return {
		student: JSON.stringify(student)
	};
};