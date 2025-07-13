import React from 'react';
import './styles.less';
import {Button} from "antd";
import UserRoutes from "../../utilities/UserRoutes";

export default function Home() {
    return <div>
        <div className={'home-title mb-4'}>
            Hi welcome to my project
        </div>
        <div>
            This is an application that uses many AI tools to help you brainstorm ideas and turn them into prototypes.
            <br/>
            Feel free to explore.

            <br/>

            <Button href={"/user/app/" + UserRoutes.PROJECTS} type="primary" className={'mt-4'}>
                Project Page
            </Button>
        </div>
    </div>
}
