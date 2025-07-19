import React from 'react';
import {Button, Checkbox, Spin} from "antd";
import IProject from "../../../../../../interfaces/IProject";
import {useDispatch} from "react-redux";
import {StatusEnum} from "../../../../../../enums/StatusEnum";

export default function ProjectIdeatingRequestData({project}: { project: IProject }) {

    const dispatch = useDispatch();

    const [selectedIdeas, setSelectedIdeas] = React.useState<number[]>([]);

    const goToNextStatus = () => {
        dispatch({
            type: 'DATA_UPDATE_PROJECT_STATUS', payload: {
                id: project.id,
                status: StatusEnum.QUEUED,
                extra: {
                    selected_ideas: selectedIdeas
                }
            }
        });
    }

    return project ? <div>
            <div className={'d-flex justify-content-start mb-4 text-bold'}>
                Please choose the best ideas below for prototyping.
            </div>
            <div>
                {
                    project.project_ideas.map(idea => (
                        <div key={idea.id} className={'mt-2'}>
                            <Checkbox
                                className={'mr-2'}
                                id={`idea-${idea.id}`}
                                name={`idea-${idea.id}`}
                                value={idea.id}
                                checked={selectedIdeas.includes(idea.id)}
                                onChange={(e) => {
                                    const id = parseInt(e.target.value);
                                    setSelectedIdeas(prev => {
                                        if (prev.includes(id)) {
                                            return prev.filter(i => i !== id);
                                        } else {
                                            return [...prev, id];
                                        }
                                    });
                                }}
                            />
                            <span className={'text-bold'}>{idea.title}</span>
                            <span className={'text-muted'}> ({idea.description})</span>
                        </div>
                    ))
                }

                <div className={'mt-4'}>
                    <Button type={'primary'} onClick={() => goToNextStatus()}>
                        Start prototyping
                    </Button>
                </div>
            </div>
        </div>
        : <Spin/>
}
