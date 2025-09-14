import React from 'react';
import {Button, Spin, Table} from "antd";
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
            <div className={'d-flex justify-content-start mb-4 h6 text-bold'}>
                Please choose the best ideas below for prototyping.
            </div>
            <div>
                <Table
                    dataSource={project.project_ideas}
                    rowKey="id"
                    pagination={false}
                    rowSelection={{
                        type: 'checkbox',
                        selectedRowKeys: selectedIdeas,
                        onChange: (selectedRowKeys: React.Key[]) => {
                            setSelectedIdeas(selectedRowKeys as number[]);
                        },
                    }}
                >
                    <Table.Column
                        title={'Title'}
                        dataIndex={'title'}
                        key={'title'}
                        render={(text: string) => <span className={'text-bold'}>{text}</span>}
                    />
                    <Table.Column
                        title={'Description'}
                        dataIndex={'description'}
                        key={'description'}
                        render={(text: string) => <span className={'text-muted'}>{text}</span>}
                    />

                </Table>

                <div className={'mt-4'}>
                    <Button type={'primary'} onClick={() => goToNextStatus()}>
                        Start prototyping
                    </Button>
                </div>
            </div>
        </div>
        : <Spin/>
}
