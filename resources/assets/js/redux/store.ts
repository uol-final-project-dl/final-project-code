// noinspection SpellCheckingInspection

import {configureStore} from '@reduxjs/toolkit'
import createSagaMiddleware from "redux-saga";

import rootReducer from "./reducers";
import generalSaga from './sagas/general'
import authSaga from './sagas/auth'
import projectSaga from './sagas/project'

const sagaMiddleware = createSagaMiddleware()
const store = configureStore({
    reducer: rootReducer,
    middleware: [sagaMiddleware],
})

sagaMiddleware.run(generalSaga)
sagaMiddleware.run(authSaga)
sagaMiddleware.run(projectSaga)

export default store;
