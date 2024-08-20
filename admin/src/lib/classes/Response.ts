export class Response {
    status: string = '';
    message: string = '';
    messageType: string = '';

    constructor() {
        this.status = 'error';
        this.messageType = `variant-filled-error`;
    }

    public static sendResponse(response: any) {
        if (response === undefined) {
            response = {
                status: '',
                message: 'error',
                messageType: 'error'
            }
        }

        if (!response.status) response.status = '';
        if (!response.message) response.message = 'error';
        response.messageType = response.messageType ? `variant-filled-${response.messageType}` : `variant-filled-error`;

        return response;
    }
}