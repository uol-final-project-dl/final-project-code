import React from 'react';
import {Button, Form, Input} from "antd";
import {useDispatch} from "react-redux";
import axios from "axios";

export default function Signup() {
    const dispatch = useDispatch();
    const errorText = 'Please check your details and try again';

    const [form] = Form.useForm();

    const handleSignup = async () => {
        try {
            const values = await form.validateFields();

            const json = await axios.post('/api/user/postSignup', {
                name: values.name,
                email: values.email,
                password: values.password,
                password_confirmation: values.passwordConfirmation,
            }).then(response => {
                return response.data
            });

            if (json.result === 1) {
                dispatch({type: 'FETCH_AUTH_STATUS'})
            } else {
                form.setFields([
                    {
                        name: 'password',
                        errors: [errorText],
                    }
                ]);
            }
        } catch (error) {
            console.warn('Validation failed:', error);
        }
    }

    return <div id="landing-page" className="login-landing">
        <div className={'container'}>
            <div className="row">
                <div className="col-12 col-md-6 col-lg-5 login-process">
                    <h1 className="first-header">Welcome to Brainstorm to Prototype!</h1>
                    <p className="second-header">Create an account below:</p>
                    <Form form={form} onFinish={handleSignup}>
                        <Form.Item
                            validateTrigger="onBlur"
                            label="Name"
                            name="name"
                            rules={[
                                {required: true, message: 'Please enter your name'},
                            ]}
                            style={{
                                maxWidth: 400,
                                width: '100%'
                            }}
                        >
                            <Input
                                placeholder="Enter your Name"
                            />
                        </Form.Item>
                        <Form.Item
                            validateTrigger="onBlur"
                            label="Email"
                            name="email"
                            rules={[
                                {required: true, message: 'Please enter your email'},
                                {type: 'email', message: 'Please enter a valid email'},
                            ]}
                            style={{
                                maxWidth: 400,
                                width: '100%'
                            }}
                        >
                            <Input
                                type="email"
                                placeholder="Enter your username"
                            />
                        </Form.Item>
                        <Form.Item
                            label="Password"
                            name="password"
                            rules={[
                                {required: true, type: 'string', message: 'Please enter your password'},
                            ]}
                            style={{
                                maxWidth: 400,
                                width: '100%'
                            }}
                        >
                            <Input.Password
                                placeholder=""
                            />
                        </Form.Item>
                        <Form.Item
                            label="Password Confirmation"
                            name="passwordConfirmation"
                            rules={[
                                {required: true, type: 'string', message: 'Please re-enter your password'},
                            ]}
                            style={{
                                maxWidth: 400,
                                width: '100%'
                            }}
                        >
                            <Input.Password
                                placeholder=""
                            />
                        </Form.Item>
                        <div className={'mt-4 mb-3'}>
                            <Button
                                iconPosition={'end'}
                                type={'primary'}
                                danger={false}
                                htmlType={'submit'}
                            >
                                Signup
                            </Button>
                        </div>
                    </Form>
                </div>
            </div>
        </div>
    </div>
}
