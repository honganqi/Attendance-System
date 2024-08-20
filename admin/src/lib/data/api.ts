import { PUBLIC_BACKEND_URL } from '$env/static/public';
import type { RequestEvent } from '@sveltejs/kit';

interface ApiParams {
	method: string;
	event?: RequestEvent;
	data?: any;
	headers?: any;
}

const goToEndpoint = async (endpoint: string, params?: ApiParams) => {
    const response = await fetch(`${PUBLIC_BACKEND_URL}/api` + endpoint, {
        method: params?.method || 'get',
        mode: "cors",
        //cache: "no-cache",
        headers: params?.headers,
		body: params?.data && JSON.stringify(params.data),
		//referrer: 'localhost:5173',
        //redirect: "follow",
        //referrerPolicy: "no-referrer",
    });

	try {
		if (response.ok) {
			const json = await response.json();
			return json;
		}
	}
	catch (err) {
		console.log(err);
	}
}

export { goToEndpoint }