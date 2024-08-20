import { goToEndpoint } from "$lib/data/api";

export class Attendance {
    urlDate!: string;

    constructor(urlDate: string = '') {
        this.urlDate = urlDate;
    }
    
    async getLogs() {
        try {
            const data = await goToEndpoint(`/attendance/logs/?date=${this.urlDate}`, {
                method: 'get'
            });
            if (data) {
                return {
                    logs: data
                }
            }
        }
        catch (error) {
            console.log(error);
        }
    }

    async getStudentLogs(studentId: string) {
        try {
            const logs = await goToEndpoint(`/attendance/logs/student/?date=${this.urlDate}&student=${studentId}`, {
                method: 'get'
            })
            if (logs) {
                return logs
            }
        }
        catch (error) {

        }
    }
}