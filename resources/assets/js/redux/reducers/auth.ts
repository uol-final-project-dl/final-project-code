const initialState = {
    isLoggedIn: false,
    isTfaValid: false,
    tfaQRCode: null,
    tfaTextCode: null,
    isUserValid: false,
    loading: true,
};

export default function (state = initialState, action: any) {
    switch (action.type) {
        case "SET_AUTH_IS_LOGGED_IN": {
            const {isLoggedIn} = action.payload;
            return {
                ...state,
                isLoggedIn: isLoggedIn,
            };
        }

        case "SET_AUTH_IS_TFA_VALID": {
            const {isTfaValid, tfaQRCode, tfaTextCode} = action.payload;

            return {
                ...state,
                isTfaValid: isTfaValid,
                tfaQRCode: tfaQRCode ?? null,
                tfaTextCode: tfaTextCode ?? null
            };
        }

        case "SET_AUTH_LOADING": {
            const {loading} = action.payload;
            return {
                ...state,
                loading: loading,
            };
        }

        case "SET_AUTH_IS_USER_VALID": {
            const {isUserValid} = action.payload;
            return {
                ...state,
                isUserValid: isUserValid
            };
        }

        default:
            return state;
    }
}
