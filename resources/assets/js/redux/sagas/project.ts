import {all, put, takeLatest} from 'redux-saga/effects'
import axios from "axios";

function* fetchProject(action: any): any {
    try {
        const json = yield axios.get('/api/project/' + action.payload.id).then(
            response => {
                return response.data;
            }
        ).catch(error => {
            console.error('Error fetching project:', error);
        });

        if (json.project && typeof json.project === 'object') {
            yield put({type: 'SET_PROJECT', payload: {project: json.project}});
        }

    } catch (e) {
    }
}

function* updateProjectStatus(action: any): any {
    try {
        const json = yield axios.post('/api/project/' + action.payload.id + '/update-status',
            {
                status: action.payload.status,
                extra: action.payload.extra ?? {}
            }).then(
            response => {
                return response.data;
            }
        ).catch(error => {
            console.error('Error fetching project:', error);
        });

        if (json.project && typeof json.project === 'object') {
            yield put({type: 'SET_PROJECT', payload: {project: json.project}});
        }

    } catch (e) {
    }
}

function* updateProjectStage(action: any): any {
    try {
        const json = yield axios.post('/api/project/' + action.payload.id + '/update-stage',
            {
                stage: action.payload.stage
            }).then(
            response => {
                return response.data;
            }
        ).catch(error => {
            console.error('Error updating project stage:', error);
        });

        if (json.project && typeof json.project === 'object') {
            yield put({type: 'SET_PROJECT', payload: {project: json.project}});
        }

    } catch (e) {
    }
}

function* watchFetchProject() {
    yield takeLatest("DATA_FETCH_PROJECT", fetchProject);
}

function* watchUpdateProjectStatus() {
    yield takeLatest("DATA_UPDATE_PROJECT_STATUS", updateProjectStatus);
}

function* watchUpdateProjectStage() {
    yield takeLatest("DATA_UPDATE_PROJECT_STAGE", updateProjectStage);
}

export default function* rootSaga() {
    yield all([
        watchFetchProject(),
        watchUpdateProjectStatus(),
        watchUpdateProjectStage(),
    ])
}

