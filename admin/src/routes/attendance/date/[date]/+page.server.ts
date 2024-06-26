import { fail } from '@sveltejs/kit';
import type { PageServerLoad } from "../../$types";
import { BACKEND_URL } from '$env/static/private';

/** @type {import('@sveltejs/kit').Load} */
export const load: PageServerLoad = async ({ params }) => {
	const urlDate = params.date;

    const getDateEntries = async () => {
		const response = await fetch(`http://${BACKEND_URL}/api/attendance/logs/?date=${urlDate}`, {
			method: "GET",
			mode: "cors",
			cache: "no-cache",
			headers: {
				"Content-Type": "application/json",
			},
			redirect: "follow",
			referrerPolicy: "no-referrer",
			//body: JSON.stringify({id: this.id})
		});
		const data = await response.json();

		if (data) {
			return data;
		}

		// const logs = await prisma.$queryRaw`
		// SELECT
		// 	MIN(CASE WHEN userAction = 0 THEN timeEntry ELSE NULL END) AS "in",
		// 	MAX(CASE WHEN userAction = 1 THEN timeEntry ELSE NULL END) AS "out",
		// 	nickname as name,
		// 	CONCAT(lastname, ', ', firstname, IF (suffix IS NULL or suffix = '', '', CONCAT(' ', suffix)), IF (middlename IS NULL or middlename = '', '', CONCAT(' ', SUBSTR(middlename, 1, 1), '.'))) as fullname
		// FROM
		// 	attendance
		// JOIN students ON attendance.student = students.id
		// WHERE DATE(timeEntry) = ${selectedDate}
		// GROUP BY
		// 	DATE(timeEntry)
		// ORDER BY
		// 	timeEntry;
		// `;
        // if (!logs) {
        //     return fail(404, { message: "No records found!" })
        // }
        return fail(404, { message: "No records found!" })
    }

	return {
		logs: await getDateEntries(),
	}
};