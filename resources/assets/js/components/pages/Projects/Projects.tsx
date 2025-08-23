import React, {useEffect} from 'react';
import './styles.less';
import axios from "axios";
import IProject from "../../../interfaces/IProject";
import {Button, Table} from "antd";
import UserRoutes, {fullRoute} from "../../utilities/UserRoutes";
import {useNavigate} from "react-router-dom";

export default function Projects() {
    const [projects, setProjects] = React.useState<IProject[]>([]);
    const navigate = useNavigate();

    useEffect(() => {
        axios.get('/api/projects').then(
            response => {
                const data = response.data;
                if (data.projects && Array.isArray(data.projects)) {
                    setProjects(data.projects);
                }
            }
        ).catch(error => {
            console.error('Error fetching projects:', error);
        })
    }, []);

    return <div>
        <div className={'d-flex justify-content-end mb-4'}>
            <Button type="primary" onClick={() => navigate(fullRoute(UserRoutes.PROJECTS) + '/create')}>
                Create New Project
            </Button>
        </div>

        <Table
            dataSource={projects}
            rowKey="id"
            pagination={{pageSize: 10}}
            bordered
            size="middle"
        >
            <Table.Column title="Project Name" dataIndex="name" key="name"/>
            <Table.Column title="Created At" dataIndex="created_at" key="created_at"
                          render={(text: string) => new Date(text).toLocaleDateString()}/>
            <Table.Column title="Actions" key="actions"
                          render={(_text: string, record: IProject) => (
                              <span>
                                  <Button
                                      type={'default'}
                                      className={'me-2'}
                                      onClick={() => navigate(fullRoute(UserRoutes.PROJECTS) + '/' + record.id)}
                                  >
                                      View
                                  </Button>
                                  <Button
                                      variant={'filled'}
                                      color={'danger'}
                                      onClick={() => {
                                          let confirmed = confirm('Are you sure you want to delete this project?')
                                          if (confirmed) {
                                              axios.delete(`/api/project/${record.id}`)
                                                  .then(() => {
                                                      window.location.reload();
                                                  });
                                          }
                                      }
                                      }>
                                      Delete
                                  </Button>
                              </span>
                          )}/>

        </Table>
    </div>
}
