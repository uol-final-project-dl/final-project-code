import {all, put, takeLatest} from 'redux-saga/effects'
import axios from "axios";

function* fetchData(): any {
    try {
        const json = yield axios.get(`/api/getData`)
            .then(response => response.data,);

        yield put({type: "SET_DATA", payload: json});

        yield put({type: "STOP_LOADING"});

    } catch (e) {
        yield put({type: "STOP_LOADING"});
    }
}

function* watchFetchData() {
    yield takeLatest("DATA_FETCH_REQUESTED", fetchData);
}

export default function* rootSaga() {
    yield all([
        watchFetchData(),
    ])
}
