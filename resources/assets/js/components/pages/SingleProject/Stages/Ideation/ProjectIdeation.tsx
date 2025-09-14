import React, {useEffect} from 'react';
import './styles.less';
import {Spin} from "antd";
import IProject from "../../../../../interfaces/IProject";
import {useSelector} from "react-redux";
import {StatusEnum} from "../../../../../enums/StatusEnum";
import ProjectIdeatingRequestData from "./StatusRequestData/ProjectIdeatingRequestData";
import {LoadingOutlined} from "@ant-design/icons";

export default function ProjectIdeation() {
    const project = useSelector((state: { project: { project: IProject | null } }) => state.project.project);
    useEffect(() => {

    }, []);

    const projectStageStatus = () => {
        switch (project?.status) {
            case StatusEnum.REQUEST_DATA:
                return <div>
                    <ProjectIdeatingRequestData project={project}/>
                </div>;
            case StatusEnum.QUEUED:
                return <div className={'d-flex flex-column align-items-center justify-content-center'}>
                    <span className={'h5 mb-2'} style={{color: '#83730e'}}>Queued</span>
                    <span className={'h6 mb-2'}>Please wait while we process the ideas...</span>
                    <Spin indicator={<LoadingOutlined style={{fontSize: 48}} spin/>}/>
                </div>
            case StatusEnum.READY:
                return <div>
                    <span className={'text-success h5'}>Ready for prototyping</span>
                </div>;
            default:
                return <div>
                    <span className={'text-danger h5'}>Unknown status</span>
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
