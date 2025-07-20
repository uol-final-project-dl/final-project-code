import React, {useEffect} from 'react';
import './styles.less';
import IProject from "../../../interfaces/IProject";
import {Spin} from "antd";
import {useParams} from "react-router-dom";
import ProjectBrainstorming from "./Stages/Brainstorming/ProjectBrainstorming";
import {useDispatch, useSelector} from "react-redux";
import {ProjectStageEnum} from "../../../enums/ProjectStageEnum";
import ProjectIdeation from "./Stages/Ideation/ProjectIdeation";
import ProjectPrototyping from "./Stages/Prototyping/ProjectPrototyping";

export default function SingleProject() {
    const project = useSelector((state: { project: { project: IProject | null } }) => state.project.project);
    const dispatch = useDispatch();
    const {id} = useParams<{ id: string }>();

    useEffect(() => {
        dispatch({type: 'DATA_FETCH_PROJECT', payload: {id: id}});
    }, []);

    const projectStageSection = () => {
        switch (project?.stage) {
            case ProjectStageEnum.BRAINSTORMING:
                return <ProjectBrainstorming/>;
            case ProjectStageEnum.IDEATING:
                return <ProjectIdeation/>;
            case ProjectStageEnum.PROTOTYPING:
                return <ProjectPrototyping/>;
            case ProjectStageEnum.CODING:
                return <div>Coding Stage</div>;
            default:
                return <div>Unknown Stage</div>;
        }
    }

    return project ? <div>
            <div className={'d-flex justify-content-start mb-4 text-bold'}>
                {project.name}
            </div>

            <div>
                {projectStageSection()}
            </div>
        </div>
        : <Spin/>
}
