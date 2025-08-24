import React from 'react';
import './styles.less';
import {Button, Divider, Form, Radio} from "antd";
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
                            <Divider className={'mt-3 mb-2'}>
                                Provider Hosted Models
                            </Divider>
                            <br/>
                            <Radio value="openai">OpenAI (Ideation: gpt-4o-mini & Coding: gpt-4.1)</Radio>
                            <br/>
                            <Radio value="anthropic">Anthropic (Ideation: Claude Haiku 3.5 & Coding: Claude Sonnet
                                4)</Radio>
                            <br/>
                            <Radio value="google">Google (Ideation: Gemini 2.5 Flash & Coding: Gemini 2.5 Pro)</Radio>
                            <br/>
                            <Divider className={'mt-3 mb-2'}>
                                Open Source Models (Self-hosted)
                            </Divider>
                            <Radio value="llama-local">LLama 3.1 (Ideation & Coding:
                                llama3.1:8b-instruct-q4_K_M)</Radio>
                            <br/>
                            <Radio value="qwen-local">Qwen 2.5 (Ideation: qwen2.5:7b-instruct-q4_K_M & Coding:
                                qwen2.5-coder:7b-instruct-q4_K_M )</Radio>
                            <br/>
                            <Radio value="deepseek-local">Deepseek (Ideation: deepseek-v2:16b-lite-chat-q4_K_M & Coding:
                                deepseek-coder-v2:16b-lite-instruct-q4_K_M)</Radio>
                            <br/>
                            <Divider className={'mt-3 mb-2'}>
                                Open Source Models (API hosted by Fireworks AI)
                            </Divider>
                            <Radio value="llama">LLama 3.1 (Ideation & Coding: llama-v3p1-405b-instruct)</Radio>
                            <br/>
                            <Radio value="qwen">Qwen 3 (Ideation: qwen3-235b-a22b-instruct-2507 & Coding:
                                qwen3-coder-480b-a35b-instruct)</Radio>
                            <br/>
                            <Radio value="deepseek">Deepseek 3.1 (Ideation & Coding:
                                deepseek-v3p1)</Radio>
                            <br/>
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
