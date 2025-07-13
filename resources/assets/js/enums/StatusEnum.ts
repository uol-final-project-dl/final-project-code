export enum StatusEnum {
    REQUEST_DATA = 'request_data',
    QUEUED = 'queued',
    READY = 'ready',
}

export const getStatusLabel = (status: StatusEnum): string => {
    switch (status) {
        case StatusEnum.REQUEST_DATA:
            return 'Request Data';
        case StatusEnum.QUEUED:
            return 'Queued';
        case StatusEnum.READY:
            return 'Ready';
        default:
            return 'Unknown Status';
    }
}
