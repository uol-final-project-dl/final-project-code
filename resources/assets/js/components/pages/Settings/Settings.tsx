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
                            <Radio value="openai">OpenAI</Radio>
                            <Radio value="anthropic">Anthropic</Radio>
                            <Radio value="google">Google</Radio>
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
