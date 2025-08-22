import React, {useRef} from 'react'
import {Route, Routes} from "react-router";

import ToastContainer from "./utilities/ToastContainer/ToastContainer";
import Home from './pages/Home/Home'
import UserRoutes from "./utilities/UserRoutes";
import Layout from "./pages/layout/Layout";
import Projects from "./pages/Projects/Projects";
import ProjectForm from "./pages/Projects/ProjectForm";
import SingleProject from "./pages/SingleProject/SingleProject";
import Settings from "./pages/Settings/Settings";
import {useSelector} from "react-redux";

export default function AppRoutes() {
    const mainSectionContainerRef = useRef<HTMLDivElement>(null)
    const pusher = useSelector((state: { generals: { pusher: any } }) => state.generals.pusher);
    const userId = useSelector((state: { generals: { userId: any } }) => state.generals.userId);

    React.useEffect(() => {
        if (pusher && userId) {
            const channel = pusher.subscribe('private-user-channel-' + userId);
            channel.bind('reload-event', function () {
                window.location.reload();
            });
        }
    }, [pusher, userId]);

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

                            <Route
                                path="user/app/settings"
                                element={
                                    <Layout selectedKey={UserRoutes.SETTINGS} title={"Settings"}>
                                        <Settings/>
                                    </Layout>
                                }
                            />

                            { /*Project List Page */}
                            <Route
                                path="user/app/projects"
                                element={
                                    <Layout selectedKey={UserRoutes.PROJECTS} title={"Projects"}>
                                        <Projects/>
                                    </Layout>
                                }
                            />

                            <Route
                                path="user/app/projects/create"
                                element={
                                    <Layout selectedKey={UserRoutes.PROJECTS} title={"Project Create"}>
                                        <ProjectForm/>
                                    </Layout>
                                }
                            />

                            { /* Single Project Page */}

                            <Route
                                path={"user/app/projects/:id"}
                                element={
                                    <Layout selectedKey={UserRoutes.PROJECTS} title={"Project Details"}>
                                        <SingleProject/>
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
