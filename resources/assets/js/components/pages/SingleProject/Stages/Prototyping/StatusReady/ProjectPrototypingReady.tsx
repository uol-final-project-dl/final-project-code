import React from 'react';
import {Button, Form, Input, Modal, Spin, Table} from "antd";
import IProject, {IPrototype} from "../../../../../../interfaces/IProject";
import {getStatusLabel, StatusEnum} from "../../../../../../enums/StatusEnum";
import axios from "axios";
import {useDispatch} from "react-redux";

interface IPrototypeForTable {
    id: number;
    name: string;
    description: string;
    status: string;
    uuid: string;
    created_at: string;
}

export default function ProjectPrototypingReady({project}: { project: IProject }) {
    const [openRemixModalPrototypeId, setOpenRemixModalPrototypeId] = React.useState<number | null>(null);
    const [currentRemixDescription, setCurrentRemixDescription] = React.useState<string>('');
    const dispatch = useDispatch();

    const prototypes = () => {
        let prototypes: IPrototypeForTable[] = [];

        project.project_ideas.forEach(idea => {
            idea.prototypes.forEach(prototype => {
                prototypes.push({
                    id: prototype.id,
                    name: idea.title,
                    description: idea.description,
                    status: getStatusLabel(prototype.status as StatusEnum),
                    uuid: prototype.uuid,
                    created_at: prototype.created_at
                });
            })
        })
        return prototypes;
    }

    const submitRemix = () => {
        if (openRemixModalPrototypeId === null || currentRemixDescription.trim() === '') {
            return;
        }

        axios.post('/api/project/' + project.id + '/prototype/' + openRemixModalPrototypeId + '/remix', {
            description: currentRemixDescription
        }).then(
            () => {
                setOpenRemixModalPrototypeId(null);
                setCurrentRemixDescription('');
                dispatch({type: 'DATA_FETCH_PROJECT', payload: {id: project.id}});
            }
        ).catch(
            (error) => {
                console.error('Error remixing prototype:', error);
            }
        );
    }

    const retryPrototype = (prototypeId: number) => {
        axios.get('/api/project/' + project.id + '/prototype/' + prototypeId + '/retry')
            .then(() => {
                dispatch({type: 'DATA_FETCH_PROJECT', payload: {id: project.id}});
            })
            .catch((error) => {
                console.error('Error retrying prototype:', error);
            });
    }

    return project ? <div>
            <div className={'d-flex justify-content-start mb-4 text-bold'}>
                Please find below the prototypes
            </div>

            <Table
                dataSource={prototypes()}
                rowKey="id"
                pagination={{pageSize: 10}}
                bordered
                size="middle"
            >
                <Table.Column title="Prototype Name" dataIndex="name" key="name"/>
                <Table.Column title="Prototype Description" dataIndex="description" key="description"/>
                <Table.Column title="Created At" dataIndex="created_at" key="created_at"
                              render={(text: string) => new Date(text).toLocaleDateString()}/>
                <Table.Column title="Status" dataIndex="status" key="status"/>
                <Table.Column title="Actions" key="actions"
                              render={(_text: string, record: IPrototype) => (
                                  <div>
                                      {project.github_repository_id ? <div>
                                              {record.status === getStatusLabel(StatusEnum.READY) ?
                                                  <div>
                                                      <Button
                                                          type={'default'}
                                                          className={'me-2'}
                                                          target={'_blank'}
                                                          href={'/branch/' + record.id}
                                                      >
                                                          Open Branch
                                                      </Button>
                                                  </div>
                                                  : null}
                                          </div>
                                          :
                                          <div>
                                              {record.status === getStatusLabel(StatusEnum.READY) ?
                                                  <div>
                                                      <Button
                                                          type={'default'}
                                                          className={'me-2'}
                                                          target={'_blank'}
                                                          href={'/prototype/' + record.id}
                                                      >
                                                          View
                                                      </Button>

                                                      <Button
                                                          type={'default'}
                                                          className={'me-2'}
                                                          onClick={() => {
                                                              setOpenRemixModalPrototypeId(record.id);
                                                              setCurrentRemixDescription('');
                                                          }}
                                                      >
                                                          Remix
                                                      </Button>

                                                      <Modal
                                                          open={openRemixModalPrototypeId === record.id}
                                                          onCancel={() => {
                                                              setOpenRemixModalPrototypeId(null)
                                                              setCurrentRemixDescription('');
                                                          }}
                                                          okButtonProps={{hidden: true}}
                                                          cancelButtonProps={{hidden: true}}
                                                          title={'Remix Prototype'}
                                                      >
                                                          <div>
                                                              <Form.Item>
                                                                  <Input.TextArea
                                                                      placeholder={'Remix description'}
                                                                      onChange={(e) => {
                                                                          setCurrentRemixDescription(e.target.value);
                                                                      }}
                                                                  >
                                                                      {currentRemixDescription}
                                                                  </Input.TextArea>
                                                              </Form.Item>
                                                              <Button
                                                                  type={'primary'}
                                                                  onClick={() => {
                                                                      submitRemix();
                                                                  }}
                                                              >
                                                                  Submit Remix
                                                              </Button>
                                                          </div>
                                                      </Modal>
                                                  </div>
                                                  : null}
                                              {record.status === getStatusLabel(StatusEnum.FAILED) ?
                                                  <Button
                                                      variant={'filled'}
                                                      color={'danger'}
                                                      onClick={() => retryPrototype(record.id)}
                                                  >
                                                      Retry
                                                  </Button>
                                                  : null
                                              }
                                          </div>
                                      }
                                  </div>
                              )}/>

            </Table>
        </div>
        : <Spin/>
}
