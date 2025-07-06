import React, {useRef} from 'react'
import {Route, Routes} from "react-router";

import ToastContainer from "./utilities/ToastContainer/ToastContainer";
import Home from './pages/Home/Home'
import UserRoutes from "./utilities/UserRoutes";
import Layout from "./pages/layout/Layout";

export default function AppRoutes() {
    const mainSectionContainerRef = useRef<HTMLDivElement>(null)

    return (
        <>
            <ToastContainer/>
            <div className={'dashboard-container'}>
                <div className={'user-dashboard'}>
                    <div className="main-section-container" ref={mainSectionContainerRef}>
                        <Routes>
                            <Route
                                path="user/app"
                                element={
                                    <Layout selectedKey={UserRoutes.HOME} title={"Home"}>
                                        <Home/>
                                    </Layout>
                                }
                            />
                            <Route
                                path="user/app/home"
                                element={
                                    <Layout selectedKey={UserRoutes.HOME} title={"Home"}>
                                        <Home/>
                                    </Layout>
                                }
                            />
                        </Routes>
                    </div>
                </div>
            </div>
        </>
    )
}
