import Pusher from 'pusher-js';

export default class PusherHelper {
    static async initPusher() {
        const csrf = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

        // To replace with env variable if needed
        return new Pusher((document.head.querySelector('meta[name="pusher-key"]') as HTMLMetaElement).content ?? '', {
            cluster: 'eu',
            authEndpoint: '/api/v1/pusher/user',
            auth: {
                headers: {
                    'X-XSRF-TOKEN': csrf,
                },
            },
        });
    }
}
