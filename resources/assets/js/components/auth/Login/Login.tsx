import React from 'react';
import './styles.less';
import {Button, Form, Input} from "antd";
import {useDispatch} from "react-redux";
import axios from "axios";
import Signup from "../Signup/Signup";

export default function Login() {
    const dispatch = useDispatch();
    const [showTfa, setShowTfa] = React.useState(false);
    const [signup, setSignup] = React.useState(false);
    const errorText = 'Please check your login details and try again';

    const [form] = Form.useForm();

    const handleLogin = async () => {
        try {
            const values = await form.validateFields();

            const json = await axios.post('/api/user/postLogin', {
                email: values.email,
                password: values.password,
                verificationCode: showTfa ? values.verifyCode : null
            }).then(response => {
                return response.data
            });
            if (json.result === 1) {
                dispatch({type: 'FETCH_AUTH_STATUS'})
            }

            if (json?.result === 0) {
                form.setFields([
                    {
                        name: 'password',
                        errors: [errorText],
                    }
                ]);
            }

            if (json.result === 2) {
                if (json?.tfaSetupNeeded) {
                    dispatch({type: 'FETCH_AUTH_STATUS'})
                } else {
                    setShowTfa(true);
                }

            }
        } catch (error) {
            console.warn('Validation failed:', error);
        }
    }

    async function onForgotPassword(e: React.MouseEvent<HTMLAnchorElement>) {
        e.preventDefault();
        window.location.href = '/user/password';
    }

    return signup ? <Signup/>
        : <div id="landing-page" className="login-landing">
            <div className={'container'}>
                <div className="row">
                    <div className="col-12 col-md-6 col-lg-5 login-process">
                        <h1 className="first-header">Welcome to Brainstorm to Prototype!</h1>
                        <p className="second-header">Sign in below:</p>
                        <Form form={form} onFinish={handleLogin}>
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

                            {
                                showTfa && <Form.Item
                                    label="Verification Code"
                                    name="verifyCode"
                                    rules={[
                                        {required: true, message: 'Please enter your 2fa code'},
                                        {min: 6, message: 'The code must be at least 6 characters long'},
                                    ]}
                                    style={{
                                        maxWidth: 400,
                                        width: '100%'
                                    }}
                                >
                                    <Input.Password/>
                                </Form.Item>
                            }

                            <div className={'mt-4 mb-3'}>
                                <Button
                                    iconPosition={'end'}
                                    type={'primary'}
                                    danger={false}
                                    htmlType={'submit'}
                                >
                                    Login
                                </Button>
                            </div>
                            <div className="form-group text-left">
                                <div className="col-md-12 text-left password-link">
                                    <Button onClick={onForgotPassword} className="password-forgot">
                                        Forgot your password?
                                    </Button>
                                </div>

                                <div className="col-md-12">
                                    <Button onClick={() => setSignup(true)}>
                                        Signup
                                    </Button>
                                </div>
                            </div>
                        </Form>
                    </div>
                </div>
            </div>
        </div>
}
