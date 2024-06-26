import { BACKEND_URL } from "$env/static/private";

export class Student {
    id!: string;
    lastname!: string;
    firstname!: string;
    middlename!: string;
    suffix!: string;
    nickname!: string;
    birthdate!: Date;
    gender!: string;
	status!: boolean;
    fullname!: string;
    splitName!: {
        lastname: string,
        firstname: string,
        middlename: string,
        suffix: string,
    }
	idnumber!: string;

    constructor(studentId: string = '') {
		const nameArray = {
			lastname: '',
			firstname: '',
			middlename: '',
			suffix: '',
		}
		if (studentId) {
			this.id = studentId;
		}
    }

	async getList() {
		const response = await fetch(`http://${BACKEND_URL}/api/student/getList`, {
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
	}

    async getRecord() {
		try {
			const response = await fetch(`http://${BACKEND_URL}/api/student/get/?id=${this.id}`, {
				method: "GET",
				mode: "cors",
				cache: "no-cache",
				headers: {
					"Content-Type": "application/json",
				},
				redirect: "follow",
				referrerPolicy: "no-referrer",
			});
			const data = await response.json();
			if (data) {
				if (data.birthdate) {
					this.birthdate = new Date(data.birthdate)
				}
				Object.assign(this, data);

				const nameArray = {
					lastname: this.lastname,
					firstname: this.firstname,
					middlename: this.middlename,
					suffix: this.suffix
				}

				this.makeName(nameArray);

				return data;
			}
	
		}
		catch (error) {
			console.log(error)
		}
	}

	makeName(nameArray: {lastname: string, firstname: string, middlename: string, suffix: string}) {
		let fullnameSplit = {
			lastname: '',
			firstname: '',
			middlename: '',
			suffix: ''
		}
		if (nameArray.middlename && nameArray.middlename.trim() != '') {
			fullnameSplit.middlename = Array.from(nameArray.middlename)[0] + '.';
		}
	
		if (nameArray.suffix && nameArray.suffix.trim() != '') {
			fullnameSplit.suffix = ' ' + nameArray.suffix;
		}
		
		if (nameArray.firstname && nameArray.lastname) {
			fullnameSplit.firstname = nameArray.firstname;
			fullnameSplit.lastname = nameArray.lastname;
			this.fullname = `${fullnameSplit.lastname}, ${fullnameSplit.firstname}${fullnameSplit.suffix} ${fullnameSplit.middlename}`;
		}

		this.splitName = {
			lastname: nameArray.lastname,
			firstname: nameArray.firstname,
			middlename: nameArray.middlename,
			suffix: nameArray.suffix
		}
	}

	async getStudentIDFromIDNumber(idnumber: string) {
		try {
			const response = await fetch(`http://${BACKEND_URL}/api/student/get/?idnumber=${idnumber}`, {
				method: "GET",
				mode: "cors",
				cache: "no-cache",
				headers: {
					"Content-Type": "application/json",
				},
				redirect: "follow",
				referrerPolicy: "no-referrer",
			});
			const data = await response.json();
			return data.student.id;
		}
		catch (err) {

		}
	}

	async attendanceEntry(idnumber: string) {
		try {
			if (idnumber != '') {
				const id = await this.getStudentIDFromIDNumber(idnumber)!;

				// yada-yada add attendance logic here
			}
		}
		catch (err) {

		}
	}

	async createRecord(newData) {
		try {
			const response = await fetch(`http://${BACKEND_URL}/api/student/create/`, {
				method: "POST",
				mode: "cors",
				cache: "no-cache",
				headers: {
					"Content-Type": "application/json",
				},
				redirect: "follow",
				referrerPolicy: "no-referrer",
				body: JSON.stringify({newData})
			});
			const data = await response.json();
			return {
				status: 201,
				message: data.message,
				messageType: `variant-filled-${data.messageType}`
			}
		}
		catch (err) {

		}
	}

	async updateRecord(newData) {
		try {
			const id = this.id;
			const response = await fetch(`http://${BACKEND_URL}/api/student/update/?id=${this.id}`, {
				method: "POST",
				mode: "cors",
				cache: "no-cache",
				headers: {
					"Content-Type": "application/json",
				},
				redirect: "follow",
				referrerPolicy: "no-referrer",
				body: JSON.stringify({id, newData})
			});
			const data = await response.json();
			return {
				status: 201,
				message: data.message,
				messageType: `variant-filled-${data.messageType}`
			}
		}
		catch (err) {

		}
	}

	async deleteRecord() {
		try {
			const id = this.id;
			const response = await fetch(`http://${BACKEND_URL}/api/student/update/?id=${this.id}`, {
				method: "POST",
				mode: "cors",
				cache: "no-cache",
				headers: {
					"Content-Type": "application/json",
				},
				redirect: "follow",
				referrerPolicy: "no-referrer",
				body: JSON.stringify({id, delete: true})
			});
			const data = await response.json();
			return {
				status: 201,
				message: data.message,
				messageType: `variant-filled-${data.messageType}`
			}
		}
		catch (err) {

		}
	}

	async updateStatus(newStatus) {
		try {
			const id = this.id;
			const response = await fetch(`http://${BACKEND_URL}/api/student/updateStatus/?id=${this.id}`, {
				method: "POST",
				mode: "cors",
				cache: "no-cache",
				headers: {
					"Content-Type": "application/json",
				},
				redirect: "follow",
				referrerPolicy: "no-referrer",
				body: JSON.stringify({id, newStatus})
			});
			const data = await response.json();
			return {
				status: 201,
				message: data.message,
				messageType: `variant-filled-${data.messageType}`
			}
		}
		catch (err) {

		}	
	}
}