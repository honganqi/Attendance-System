import { fail } from '@sveltejs/kit';
import type { PageServerLoad } from "../../../$types";
import { Student } from '$lib/classes/Student';
import { Attendance } from '$lib/classes/Attendance';

/** @type {import('@sveltejs/kit').Load} */
export const load: PageServerLoad = async ({ params }) => {
	const urlDate = params.date;
	const student = new Student(params.studentId);
	const studentData = await student.getRecord();
	const attendance = new Attendance(urlDate);
	const logs = await attendance.getStudentLogs(student.id);

	return {
		student: studentData,
		logs
	};
};