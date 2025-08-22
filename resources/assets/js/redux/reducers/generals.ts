import Pusher from "pusher-js";

const initialState = {
    pageTitle: 'Final Project',
    toast: null,
    provider: 'openai',
    githubRepositories: [],
    userId: null,
    pusher: new Pusher((document.head.querySelector('meta[name="pusher-key"]') as HTMLMetaElement).content, {
        cluster: 'eu',
        authEndpoint: '/user/pusher/auth',
        auth: {
            headers: {
                'X-CSRF-Token': (document.head.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content
            }
        }
    })
};

export default function (state = initialState, action: any) {
    switch (action.type) {
        case "SET_DATA": {
            return {
                ...state,
                githubRepositories: action.payload.githubRepositories,
                userId: action.payload.userId
            };
        }
        case "START_GENERAL_TOAST": {
            return {
                ...state,
                toast: action.payload.toast
            };
        }
        case "STOP_GENERAL_TOAST": {
            return {
                ...state,
                toast: null
            };
        }
        case "SET_PROVIDER": {
            const {provider} = action.payload;
            return {
                ...state,
                provider: provider
            };
        }
        default:
            return state;
    }
}
