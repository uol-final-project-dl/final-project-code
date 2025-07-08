enum UserRoutes {
    HOME = 'home',
    PROJECTS = 'projects',
}

export default UserRoutes;


export const fullRoute = (route: UserRoutes): string => {
    return `/user/app/${route}`;
}
