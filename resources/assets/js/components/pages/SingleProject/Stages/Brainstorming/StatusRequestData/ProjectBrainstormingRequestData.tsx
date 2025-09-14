import React from 'react';
import {Button, Spin, Upload} from "antd";
import IProject from "../../../../../../interfaces/IProject";
import {useDispatch} from "react-redux";
import {StatusEnum} from "../../../../../../enums/StatusEnum";

export default function ProjectBrainstormingRequestData({project}: { project: IProject }) {

    const dispatch = useDispatch();

    const reloadProject = () => {
        dispatch({type: 'DATA_FETCH_PROJECT', payload: {id: project.id}});
    }

    const goToNextStatus = () => {
        dispatch({type: 'DATA_UPDATE_PROJECT_STATUS', payload: {id: project.id, status: StatusEnum.QUEUED}});
    }

    return project ? <div>
            <div className={'d-flex justify-content-start mb-4 text-bold'}>
                Please upload a file or files containing the documents you want to use for brainstorming.
                <br/>
                If you are back on this page its because maybe some file didn't process correctly or no ideas were found.
            </div>
            <div>
                <Upload
                    action={"/api/project/" + project.id + "/brainstorming/upload-documents"}
                    method={"POST"}
                    headers={
                        {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    }
                    accept=".txt,.pdf,.mp4,.mp3,.png,.jpg,.jpeg"
                    multiple={true}
                    showUploadList={true}
                    onChange={(info) => {
                        if (info.file.status === 'done') {
                            reloadProject();
                        } else if (info.file.status === 'error') {
                            console.error('File(s) upload failed:', info.file.response);
                        }
                    }}
                    maxCount={5}
                >
                    <Button
                        className={'ant-btn ant-btn-primary'}
                        type={'default'}
                    >
                        Upload Request Data
                    </Button>
                </Upload>

                {project.project_documents.length > 0 ?
                    <div>
                        <div className={'mt-4'}>
                            Currently uploaded documents:
                        </div>
                        {project.project_documents.map(
                            (document, i) => (
                                <div key={i} className={'mt-2'}>
                                    <span className={'text-bold'}>{document.filename}</span>
                                    <span className={'text-muted'}> ({document.type})</span>
                                </div>
                            )
                        )}
                    </div> : null}

                <div className={'mt-4'}>
                    <Button type={'primary'} onClick={() => goToNextStatus()}>
                        Start Prototyping
                    </Button>
                </div>
            </div>
        </div>
        : <Spin/>
}
