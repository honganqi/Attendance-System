import { goToEndpoint } from "$lib/data/api";
import { redirect } from "@sveltejs/kit";

export class User {
    id!: string;
    name!: string;
    email!: string;

    static async login(credentials: {email: string, password: string}) {
        let params = {
            method: 'post',
            endpoint: '/login',
            data: credentials
        }
        const response = await goToEndpoint(params);
        const authAttempt = await response.json();
        
        let loginResponse = {
			username: '',
			error: false,
			message: ''
		}

        if (authAttempt.success) {
            return authAttempt.data;
        } else if (!authAttempt || !authAttempt.success) {
            loginResponse.error = true;
            loginResponse.message = 'invalid creds';
        } else {
            throw redirect(303, '/');
        }
    }
}