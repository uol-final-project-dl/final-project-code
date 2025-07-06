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
        default:
            return state;
    }
}
