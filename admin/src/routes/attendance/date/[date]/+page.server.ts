import { fail } from '@sveltejs/kit';
import type { PageServerLoad } from "../../$types";
import { Attendance } from '$lib/classes/Attendance';

/** @type {import('@sveltejs/kit').Load} */
export const load: PageServerLoad = async ({ params }) => {
	const urlDate = params.date;
	const attendance = new Attendance(urlDate);

	try {
		const logs = await attendance.getLogs();
		if (logs) {
			return logs;
		}
	}
	catch (error) {
		console.log(error);
	}

};