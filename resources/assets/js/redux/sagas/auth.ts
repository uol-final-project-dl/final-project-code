import {all, put, takeLatest} from 'redux-saga/effects'
import axios from "axios";

function* checkUserStatus(): any {
    try {
        yield put({type: 'SET_AUTH_LOADING', payload: {loading: true}})
        const response = yield axios.get(`/checkUserStatus`).then(response => response);

        switch (response.status) {
            case 401:
                switch (response.data.middleware) {
                    case 'auth.tfa':
                        yield put({type: 'SET_AUTH_IS_LOGGED_IN', payload: {isLoggedIn: true}});

                        if (response.data.reason === 'setup') {
                            yield put({
                                type: 'SET_AUTH_IS_TFA_VALID', payload: {
                                    isTfaValid: false,
                                    tfaQRCode: response.data.qr_code,
                                    tfaTextCode: response.data.text_code
                                }
                            });
                        } else {
                            yield put({type: 'SET_AUTH_IS_TFA_VALID', payload: {isTfaValid: false}});
                        }
                        break;
                    case 'access.users':
                        yield put({type: 'SET_AUTH_IS_TFA_VALID', payload: {isTfaValid: true}});
                        yield put({type: 'SET_AUTH_IS_LOGGED_IN', payload: {isLoggedIn: true}});
                        yield put({type: 'SET_AUTH_IS_USER_VALID', payload: {isUserValid: false}});
                        break;
                    default:
                        yield put({type: 'SET_AUTH_IS_LOGGED_IN', payload: {isLoggedIn: false}});
                        break;
                }
                break;
            case 200:
                yield put({type: 'SET_AUTH_IS_LOGGED_IN', payload: {isLoggedIn: true}});
                yield put({type: 'SET_AUTH_IS_TFA_VALID', payload: {isTfaValid: true}});
                yield put({type: 'SET_AUTH_IS_USER_VALID', payload: {isUserValid: true}});
                break;
            default:
                yield put({type: 'SET_AUTH_IS_LOGGED_IN', payload: {isLoggedIn: false}});
                yield put({type: 'SET_AUTH_IS_TFA_VALID', payload: {isTfaValid: false}});
                yield put({type: 'SET_AUTH_IS_USER_VALID', payload: {isUserValid: false}});
                break;
        }
    } catch (e) {
        yield put({type: 'START_GENERAL_TOAST', payload: {toast: {text: 'Something went wrong!', type: 'error'}}})
    }

    yield put({type: 'SET_AUTH_LOADING', payload: {loading: false}})
}

function* watchFetchAuthStatus() {
    yield takeLatest("FETCH_AUTH_STATUS", checkUserStatus);
}

export default function* rootSaga() {
    yield all([
        watchFetchAuthStatus()
    ])
}
