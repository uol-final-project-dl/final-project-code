import React, {useEffect} from 'react';
import './styles.less';
import {Spin} from "antd";
import IProject from "../../../../../interfaces/IProject";
import {useSelector} from "react-redux";
import {StatusEnum} from "../../../../../enums/StatusEnum";
import {getProjectStageLabel, ProjectStageEnum} from "../../../../../enums/ProjectStageEnum";
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
            <div className={'d-flex justify-content-start mb-4 text-bold'}>
                {getProjectStageLabel(project.stage as ProjectStageEnum)}
            </div>
            <div>
                {projectStageStatus()}
            </div>
        </div>
        : <Spin/>
}
