import { PUBLIC_BACKEND_URL } from '$env/static/public';

const goToEndpoint = async (endpoint: string, args = {}) => {
	let params = '';
	if (args) {
		params = '?' + new URLSearchParams(args).toString();
	}
    const response = await fetch(`http://${PUBLIC_BACKEND_URL}/api${endpoint}${params}`, {
        method: "GET",
        mode: "cors",
        cache: "no-cache",
        headers: {
            "Content-Type": "application/json",
        },
        redirect: "follow",
        referrerPolicy: "no-referrer",
    });

    const json = await response.json();
    return json;
}

const postToEndpoint = async (endpoint: string, data: Object = {}, args = {}) => {
	try {
		let params = '';
		if (args) {
			params = '?' + new URLSearchParams(args).toString();
		}
		const response = await fetch(`http://${PUBLIC_BACKEND_URL}/api${endpoint}${params}`, {
			method: "POST",
			mode: "cors",
			cache: "no-cache",
			headers: {
				"Content-Type": "application/json",
			},
			redirect: "follow",
			referrerPolicy: "no-referrer",
			body: JSON.stringify(data)
		});
	
		if (response.ok) {
			const json = await response.json();
			const { status, message, messageType, data } = json;
			return {
				status,
				message,
				messageType: `variant-filled-${messageType}`,
				data
			}    
		}
	}
	catch (error) {
		console.log(error);
	}

}

export { goToEndpoint, postToEndpoint }