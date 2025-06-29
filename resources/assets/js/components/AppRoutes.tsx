import React, {useRef} from 'react'
import {Route, Switch} from 'react-router-dom'

import ToastContainer from "./utilities/ToastContainer";
import Home from './Home/Home'
import UserRoutes from "./Layout/UserRoutes";
import Layout from "./Layout/Layout";

export default function AppRoutes() {
    const mainSectionContainerRef = useRef<HTMLDivElement>(null)

    return (
        <>
            <ToastContainer/>
            <div className={'dashboard-container'}>
                <div className={'user-dashboard'}>
                    <div className="main-section-container" ref={mainSectionContainerRef}>
                        <Switch>
                            <Route exact path={'user/app'}
                                   render={(props: any) =>
                                       <Layout selectedKey={UserRoutes.HOME}>
                                           <Home {...props}  />
                                       </Layout>
                                   }/>
                            <Route exact path={'user/app/home'}
                                   render={(props: any) =>
                                       <Layout selectedKey={UserRoutes.HOME}>
                                           <Home {...props}  />
                                       </Layout>
                                   }/>
                        </Switch>
                    </div>
                </div>
            </div>
        </>
    )
}
