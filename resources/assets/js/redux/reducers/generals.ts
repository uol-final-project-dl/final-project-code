const initialState = {
    pageTitle: 'Final Project',
    toast: null
};

export default function (state = initialState, action: any) {
    switch (action.type) {
        case "SET_DATA": {
            return {
                ...state,
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
        default:
            return state;
    }
}
