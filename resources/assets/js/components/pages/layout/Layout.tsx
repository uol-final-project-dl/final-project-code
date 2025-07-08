// noinspection SpellCheckingInspection

import React from 'react';
import {useNavigate} from 'react-router-dom';
import {MenuInfo} from 'rc-menu/lib/interface';
import {Content, Header} from 'antd/es/layout/layout';
import './styles.less';
import {Layout as AntdLayout, Menu} from "antd";
import Sider from 'antd/es/layout/Sider';
import {DashboardFilled, DoubleLeftOutlined, DoubleRightOutlined, StarFilled} from "@ant-design/icons";
import {ItemType, MenuItemType} from "antd/es/menu/interface";
import UserRoutes from "../../utilities/UserRoutes";

interface IProps {
    selectedKey?: UserRoutes;
    title?: string | undefined;
    children?: React.ReactNode;
}

const Layout = ({selectedKey, title, children}: IProps) => {
    const navigate = useNavigate();
    const appUrl = '/user/app/';
    const [collapsed, setCollapsed] = React.useState<boolean>(false);

    const ICON_MAP = {
        [UserRoutes.HOME]: <DashboardFilled/> as React.ReactNode,
        [UserRoutes.PROJECTS]: <StarFilled/> as React.ReactNode,
    };

    const menuItems: ItemType<MenuItemType>[] = [
        {
            key: UserRoutes.HOME,
            icon: ICON_MAP[UserRoutes.HOME] ?? <DashboardFilled/>,
            label: 'Home',
        },
        {
            key: UserRoutes.PROJECTS,
            icon: ICON_MAP[UserRoutes.PROJECTS] ?? <DashboardFilled/>,
            label: 'Projects',
        },
    ]

    const handleOnCollapse = (value: boolean): void => {
        setCollapsed(value);
    }

    const handleOnClick = ({key}: MenuInfo): void => {
        navigate(appUrl + (UserRoutes[key as keyof typeof UserRoutes] || key));
    }

    return (
        <AntdLayout>
            <Sider collapsible
                   collapsed={collapsed}
                   onCollapse={handleOnCollapse}
                   trigger={collapsed ? <DoubleRightOutlined/> : <DoubleLeftOutlined/>}
                   className={'layout-sider'}
            >
                <div className={'layout-sider-logo'}>
                    <div>
                        MY LOGO
                    </div>
                </div>
                <Menu
                    mode="inline"
                    inlineCollapsed={collapsed ?? false}
                    items={menuItems}
                    selectedKeys={selectedKey ? [selectedKey] : []}
                    onClick={handleOnClick}
                />
            </Sider>
            <AntdLayout>
                <div className={'layout-header'}>
                    <Header
                        style={{
                            backgroundColor: "#fff",
                        }}
                    >
                        {title}
                    </Header>
                </div>
                <Content className={'layout-content'}>
                    {children}
                </Content>
            </AntdLayout>
        </AntdLayout>

    );
}

export default Layout;
