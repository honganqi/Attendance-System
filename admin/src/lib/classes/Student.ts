import { goToEndpoint, postToEndpoint } from "$lib/data/api";

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

	async getList(inactive: boolean = false) {
		try {
			const data = await goToEndpoint('/student/getList/', {inactive});
			if (data) {
				return data;
			}
		}
		catch (error) {
			console.log(error);
		}
	}

    async getRecord() {
		try {
			const data = await goToEndpoint('/student/get/', {id: this.id})
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
			const data = await goToEndpoint('/student/get/', {idnumber});
			if (data) {
				return data.student.id;
			}
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
			const response = await postToEndpoint('/student/create/', {newData});
			if (response) {
				const { status, message, messageType, data } = response;
				return {
					status,
					message,
					messageType,
					data
				}	
			}
		}
		catch (err) {

		}
	}

	async updateRecord(newData) {
		try {
			const response = await postToEndpoint('/student/update/', {id: this.id, newData}, {id: this.id});
			if (response) {
				const { status, message, messageType, data } = response;
				return {
					status,
					message,
					messageType,
					data
				}	
			}
		}
		catch (err) {

		}
	}

	async deleteRecord() {
		try {
			const response = await postToEndpoint('/student/update/', {id: this.id, delete: true}, {id: this.id});
			if (response) {
				const { status, message, messageType, data } = response;
				return {
					status,
					message,
					messageType,
					data
				}	
			}
		}
		catch (err) {

		}
	}

	async updateStatus(newStatus) {
		try {
			const response = await postToEndpoint('/student/updateStatus/', {id: this.id, newStatus}, {id: this.id});
			if (response) {
				const { status, message, messageType, data } = response;
				return {
					status,
					message,
					messageType,
					data
				}	
			}
		}
		catch (err) {

		}	
	}
}