import React from 'react';
import './styles.less';
import axios from "axios";
import {Button, Form, Input} from "antd";
import {useNavigate} from "react-router-dom";
import {useDispatch} from "react-redux";
import UserRoutes, {fullRoute} from "../../utilities/UserRoutes";

export default function ProjectForm() {
    const [form] = Form.useForm();
    const navigate = useNavigate();
    const dispatch = useDispatch();

    const submit = () => {
        axios.post('/api/project/create',
            form.getFieldsValue(true)
        ).then(
            response => {
                const data = response.data;
                if (data.result) {
                    dispatch({
                        type: 'START_GENERAL_TOAST',
                        payload: {toast: {text: 'Project created!', type: 'success'}}
                    });
                    navigate(fullRoute(UserRoutes.PROJECTS));
                }
            }
        ).catch(error => {
            console.error('Error saving project:', error);
        })
    };

    return <div>
        <Form
            form={form}
            layout="vertical"
            onFinish={submit}
            className={'project-form'}
        >
            <Form.Item
                label="Project Name"
                name="name"
                rules={[{required: true, message: 'Please enter the project name'}]}
            >
                <Input
                    placeholder="Enter project name"
                    autoFocus
                />
            </Form.Item>

            <Form.Item
                label={'Description (optional)'}
                name="description"
                rules={[{required: false}]}
            >
                <Input.TextArea
                    placeholder="Enter project description if you prefer to give the information in text form instead of uploading a file on a later step"
                    rows={4}
                />
            </Form.Item>

            <Form.Item
                label={'Style configuration (optional)'}
                name="style_config"
                rules={[{required: false}]}
            >
                <Input.TextArea
                    placeholder="Enter style configuration if you have any specific requirements for the project, such as color schemes, fonts, etc."
                    rows={4}
                />
            </Form.Item>

            <Button type="primary" htmlType={'submit'}>Submit</Button>
        </Form>
        <div className={'d-flex justify-content-end mb-4'}>

        </div>
    </div>
}
