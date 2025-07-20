import React from 'react';
import {Button, Spin, Table} from "antd";
import IProject from "../../../../../../interfaces/IProject";
import {getStatusLabel, StatusEnum} from "../../../../../../enums/StatusEnum";

interface IPrototypeForTable {
    id: number;
    name: string;
    description: string;
    status: string;
    created_at: string;
}

export default function ProjectPrototypingReady({project}: { project: IProject }) {

    const prototypes = () => {
        let prototypes: IPrototypeForTable[] = [];

        project.project_ideas.forEach(idea => {
            idea.prototypes.forEach(prototype => {
                prototypes.push({
                    id: prototype.id,
                    name: idea.title,
                    description: idea.description,
                    status: getStatusLabel(prototype.status as StatusEnum),
                    created_at: prototype.created_at
                });
            })
        })
        return prototypes;
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
                              render={(_text: string, record: IProject) => (
                                  <span>
                                      {record.status === getStatusLabel(StatusEnum.READY) ?
                                          <Button
                                              type={'default'}
                                              className={'me-2'}
                                              target={'_blank'}
                                              href={'/prototype/' + record.id}
                                          >
                                              View
                                          </Button>
                                          :
                                          <Button
                                              variant={'filled'}
                                              color={'danger'}
                                          >
                                              Retry
                                          </Button>
                                      }
                              </span>
                              )}/>

            </Table>
        </div>
        : <Spin/>
}
