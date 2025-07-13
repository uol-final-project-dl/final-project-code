const initialState = {
    project: null
};

export default function (state = initialState, action: any) {
    switch (action.type) {
        case "SET_PROJECT": {
            return {
                ...state,
                project: action.payload.project
            };
        }
        default:
            return state;
    }
}
