import 'react-app-polyfill/ie9'
import 'react-app-polyfill/stable'

import React, {useEffect} from 'react'
import store from "../redux/store"
import {Provider, useDispatch, useSelector} from 'react-redux'
import {createRoot} from "react-dom/client"
import {Spin} from "antd";
import {BrowserRouter} from "react-router-dom";
import AppRoutes from "./AppRoutes";
import Login from "./auth/Login/Login";

function App() {
    const dispatch = useDispatch();
    const isLoggedIn = useSelector((state: { auth: { isLoggedIn: boolean } }) => state.auth.isLoggedIn);
    const loading = useSelector((state: { auth: { loading: boolean } }) => state.auth.loading);

    const savePageRedirect = () => {
        const currentUrl = window.location.pathname;
        if (currentUrl !== '/user/login' && currentUrl !== '/user/app') {
            sessionStorage.setItem('lastUrl', currentUrl);
        }
    };

    useEffect(() => {
        savePageRedirect()
        dispatch({type: 'FETCH_AUTH_STATUS'});
        dispatch({type: 'DATA_FETCH_REQUESTED'});
    }, [])

    if (loading) {
        return <div className="spin-container top10em initial-green-spinner"><Spin size="large"/></div>
    }

    // @ts-ignore
    return <BrowserRouter>
        <>
            {!isLoggedIn ? (
                    <Login/>
                ) :
                <AppRoutes/>
            }
        </>
    </BrowserRouter>
}

const container = document.getElementById('app')
if (container) {
    const root = createRoot(container)
    root.render(<Provider store={store}><App/></Provider>)
}
