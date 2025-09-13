import React from 'react';
import {Button, Form, Input, Modal, Spin, Table} from "antd";
import IProject, {IPrototype} from "../../../../../../interfaces/IProject";
import {getStatusLabel, StatusEnum} from "../../../../../../enums/StatusEnum";
import axios from "axios";
import {useDispatch} from "react-redux";
import {StarFilled, StarOutlined} from "@ant-design/icons";

interface IPrototypeForTable {
    id: number;
    feedback_score: number | null;
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
                    feedback_score: prototype.feedback_score,
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
                <Table.Column title="Feedback" dataIndex="feedback_score" key="feedback_score"
                              render={
                                  (feedback_score: number | null, record: IPrototype) => {

                                      let feedback_score_local = 0;

                                      const handleFeedbackChange = (value: number) => {
                                          axios.post('/api/project/' + project.id + '/prototype/' + record.id + '/feedback', {
                                              feedback_score: value
                                          }).then(() => {
                                              dispatch({type: 'DATA_FETCH_PROJECT', payload: {id: project.id}});
                                          }).catch((error) => {
                                              console.error('Error submitting feedback:', error);
                                          });
                                      }

                                      if (feedback_score === null) {
                                          return (
                                              <div className={'d-flex align-items-center gap-2'} style={{width: '135px'}}>
                                                  <select
                                                      style={{marginRight: 8}}
                                                      defaultValue=""
                                                      onChange={(e) => feedback_score_local = Number(e.target.value)}
                                                  >
                                                      <option value="" disabled>Rate</option>
                                                      {[1, 2, 3, 4, 5].map(val => (
                                                          <option key={val}
                                                                  value={val}>{val} Star{val > 1 ? 's' : ''}</option>
                                                      ))}
                                                  </select>
                                                  <Button
                                                      type="primary"
                                                      size="small"
                                                      onClick={() => {
                                                          handleFeedbackChange(feedback_score_local)
                                                      }}
                                                  >
                                                      Save
                                                  </Button>
                                              </div>
                                          );
                                      }

                                      const stars = [];
                                      for (let i = 0; i < 5; i++) {
                                          if (i < feedback_score) {
                                              stars.push(<StarFilled/>);
                                          } else {
                                              stars.push(<StarOutlined/>);
                                          }
                                      }
                                      return <div style={{width: '115px'}}>{stars}</div>;
                                  }
                              }
                />
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
