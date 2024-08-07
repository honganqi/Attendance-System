import { redirect } from '@sveltejs/kit';
import type { PageServerLoad } from "./$types";

/** @type {import('@sveltejs/kit').Load} */
export const load: PageServerLoad = async ({params}) => {
	if (!Object.hasOwn(params, 'date')) {
		const currentDate = new Date();
		let year = currentDate.getFullYear() + "";
		let month = (currentDate.getMonth() + 1) + "";
		if (month.length == 1) {
			month = "0" + month;
		}
		let date = currentDate.getDate() + "";
		if (date.length == 1) {
			date = "0" + date;
		}
		let dateString = year + month + date;
	
		redirect(302, `/attendance/date/${dateString}`);
	}
};