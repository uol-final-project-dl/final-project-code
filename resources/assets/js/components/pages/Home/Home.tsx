import React from 'react';
import './styles.less';
import {Button, Card} from "antd";
import UserRoutes from "../../utilities/UserRoutes";
import {BulbOutlined, GithubOutlined, StarOutlined, ThunderboltOutlined} from "@ant-design/icons";

export default function Home() {
    return <div className={'home-page'}>
        <div className={'home-hero mb-3 d-flex align-items-center justify-content-center flex-column'}>
            <div className={'text-bold h1 mb-2'} style={{color: 'white'}}>
                Brainstorm to Prototype
            </div>
            <div className={'text-bold h5'} style={{color: 'white'}}>
                This is an application that uses many AI tools to help you brainstorm ideas and turn them into
                prototypes.
            </div>
            <div>
                <Button
                    href={"/user/app/" + UserRoutes.PROJECTS}
                    type="primary"
                    className={'mt-4'}
                    style={{textDecoration: 'none !important'}}
                    icon={<StarOutlined/>}
                    iconPosition={'start'}
                    size={'large'}
                >
                    Start a new project
                </Button>
            </div>
        </div>
        <div className={'home-content p-4'}>
            <div className={'d-flex align-items-center justify-content-center h4 mb-4'}>
                Project Highlights
            </div>
            <div className={'d-flex align-items-center justify-content-between'}>
                <Card
                    cover={<div
                        className={'d-flex align-items-center justify-content-center p-4'}
                        style={{background: '#002140'}}
                    >
                        <BulbOutlined
                            style={{fontSize: '64px', color: 'white'}}
                        />
                    </div>}
                >
                    <div className={'text-bold h5 mb-2'}>
                        Idea Generation
                    </div>
                    <p>Generate innovative ideas using AI brainstorming tools.</p>
                </Card>
                <Card
                    cover={<div
                        className={'d-flex align-items-center justify-content-center p-4'}
                        style={{background: '#002140'}}
                    >
                        <ThunderboltOutlined
                            style={{fontSize: '64px', color: 'white'}}
                        />
                    </div>}
                >
                    <div className={'text-bold h5 mb-2'}>
                        Fast Prototyping
                    </div>
                    <p>Turn your ideas into prototypes quickly with AI.</p>
                </Card>
                <Card
                    cover={<div
                        className={'d-flex align-items-center justify-content-center p-4'}
                        style={{background: '#002140'}}
                    >
                        <GithubOutlined
                            style={{fontSize: '64px', color: 'white'}}
                        />
                    </div>}
                >
                    <div className={'text-bold h5 mb-2'}>
                        Integrate with GitHub
                    </div>
                    <p>Seamlessly push changes to GitHub repositories.</p>
                </Card>
            </div>
        </div>
    </div>
}
