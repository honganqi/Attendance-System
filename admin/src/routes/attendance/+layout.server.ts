import type { PageServerLoad } from "./$types";

/** @type {import('@sveltejs/kit').Load} */
export const load: PageServerLoad = async ({params}) => {
    let selectedDate = new Date();
    let urlDate = '';
    if (params.date) {
        urlDate = params.date;
        const year = urlDate.slice(0, 4);
        const month = urlDate.slice(4, 6);
        const date = urlDate.slice(6);
        try {
            selectedDate = new Date(`${year}-${month}-${date}`);
        }
        catch (err) {

        }
        return {
            selectedDate,
            urlDate
        }
    }
};