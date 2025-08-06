enum UserRoutes {
    HOME = 'home',
    PROJECTS = 'projects',
    SETTINGS = 'settings'
}

export default UserRoutes;


export const fullRoute = (route: UserRoutes): string => {
    return `/user/app/${route}`;
}
