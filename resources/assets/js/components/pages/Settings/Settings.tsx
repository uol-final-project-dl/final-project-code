import React from 'react';
import './styles.less';
import {Button, Form, Radio} from "antd";
import {useDispatch, useSelector} from "react-redux";
import axios from 'axios';

export default function Settings() {
    const provider = useSelector((state: { generals: { provider: string } }) => state.generals.provider);
    const dispatch = useDispatch();

    const onFinish = (values: { provider: string }) => {
        axios.post('/api/settings', values)
            .then(() => {
                dispatch({type: 'FETCH_AUTH_STATUS'});
            })
            .catch(error => {
                console.error('Error updating provider:', error);
            });
    };

    return (
        <div>
            <div className="home-title mb-4">
                Settings
            </div>
            <div>
                <Form
                    initialValues={{provider}}
                    onFinish={onFinish}
                >
                    <Form.Item
                        label="Provider"
                        name="provider"
                    >
                        <Radio.Group>
                            <br/>
                            <Radio value="openai">OpenAI (Ideation: gpt-4o-mini & Coding: gpt-4.1)</Radio>
                            <br/>
                            <Radio value="anthropic">Anthropic (Ideation: Claude Haiku 3.5 & Coding: Claude Sonnet
                                4)</Radio>
                            <br/>
                            <Radio value="google">Google (Ideation: Gemini 2.5 Flash & Coding: Gemini 2.5 Pro)</Radio>
                        </Radio.Group>
                    </Form.Item>

                    <Form.Item>
                        <Button type="primary" htmlType="submit">
                            Save Settings
                        </Button>
                    </Form.Item>
                </Form>
            </div>
        </div>
    );
}
