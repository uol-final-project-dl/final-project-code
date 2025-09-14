import React, {useEffect} from 'react';
import './styles.less';
import {Spin} from "antd";
import IProject from "../../../../../interfaces/IProject";
import {useSelector} from "react-redux";
import {StatusEnum} from "../../../../../enums/StatusEnum";
import ProjectPrototypingReady from "./StatusReady/ProjectPrototypingReady";

export default function ProjectPrototyping() {
    const project = useSelector((state: { project: { project: IProject | null } }) => state.project.project);
    useEffect(() => {

    }, []);

    const projectStageStatus = () => {
        switch (project?.status) {
            case StatusEnum.QUEUED:
            case StatusEnum.READY:
                return <div>
                    <ProjectPrototypingReady project={project}/>
                </div>;
            default:
                return <div>
                    <span className={'text-danger'}>Unknown status</span>
                </div>;
        }
    }

    return project ? <div>
            <div>
                {projectStageStatus()}
            </div>
        </div>
        : <Spin/>
}
